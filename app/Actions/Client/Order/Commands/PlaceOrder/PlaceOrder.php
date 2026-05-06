<?php

namespace App\Actions\Client\Order\Commands\PlaceOrder;

use App\Enums\PaymentMethodEnum;
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
        $user = auth()->user();

        if (!$cart || $cart->items->isEmpty()) {
            return [
                'message' => 'Cart is empty',
                'order' => null,
            ];
        }

        return DB::transaction(function () use ($cart, $data, $user) {

            $order = $this->createOrder->execute($data, $user);

            [$subtotal, $foodTotal, $drinksTotal] = $this->createOrderItems->execute($order, $cart);

            $isCash = $data['payment_method'] === PaymentMethodEnum::CASH->value;
            $usedPoints = $data['used_points'] ?? 0;
            $type = $data['type'];

            $amounts = $this->calculateOrderAmounts->execute(
                subtotal: $subtotal,
                foodTotal: $foodTotal,
                drinksTotal: $drinksTotal,
                usedPoints: $usedPoints,
                type: $type,
            );

            $order->update($amounts);

            $this->handleLoyaltyPoints->execute(
                user: $user,
                order: $order,
                usedPoints: $usedPoints,
                finalTotal: $amounts['total'],
                isCash: $isCash,
            );

            $cart->items()->delete();

            return [
                'message' => 'Order placed successfully',
                'order' => $order->load('user', 'items.item', 'items.meat', 'items.removedIngredients', 'address'),
            ];
        });
    }
}
