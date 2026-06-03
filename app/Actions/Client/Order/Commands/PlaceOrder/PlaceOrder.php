<?php

namespace App\Actions\Client\Order\Commands\PlaceOrder;

use App\Enums\PaymentMethodEnum;
use App\Events\OrderPlacedBroadcast;
use App\Models\Cart;
use Illuminate\Support\Facades\DB;
use Throwable;

readonly class PlaceOrder
{
    public function __construct(
        private CreateOrder           $createOrder,
        private CreateOrderItems      $createOrderItems,
        private CalculateOrderAmounts $calculateOrderAmounts,
        private HandleLoyaltyPoints   $handleLoyaltyPoints,
    ) {}

    /**
     * @param array $data
     * @return array|string[]
     * @throws Throwable
     */
    public function execute(array $data): array
    {
        $cart = Cart::query()->findOrFail($data['cart_id']);
        $cart->load('items.item', 'items.meat', 'items.removedIngredients');
        $user = auth()->user();

        if (!$cart || $cart->items->isEmpty()) {
            return [
                'message' => 'Cart is empty',
                'order' => null,
            ];
        }

        return DB::transaction(function () use ($cart, $data, $user) {

            $order = $this->createOrder->execute($data, $user);

            [$itemsTotalIncVat, $vatBreakdown] = $this->createOrderItems->execute($order, $cart);

            $isCash = $data['payment_method'] === PaymentMethodEnum::CASH->value;
            $usedPoints = $data['used_points'] ?? 0;
            $type = $data['type'];

            $amounts = $this->calculateOrderAmounts->execute(
                itemsTotalIncVat: $itemsTotalIncVat,
                vatBreakdown: $vatBreakdown,
                usedPoints: $usedPoints,
                type: $type,
            );

            $order->update($amounts);

            $this->handleLoyaltyPoints->execute(
                user: $user,
                order: $order,
                usedPoints: $usedPoints,
                finalTotal: $amounts['total_inc_vat'],
                isCash: $isCash,
            );

            $cart->items()->delete();

            $order->load('user', 'items.item', 'items.meat', 'items.removedIngredients', 'address');

            if ($order->confirmed_at !== null) {
                event(new OrderPlacedBroadcast($order));
            }

            return [
                'message' => 'Order placed successfully',
                'order' => $order,
            ];
        });
    }
}
