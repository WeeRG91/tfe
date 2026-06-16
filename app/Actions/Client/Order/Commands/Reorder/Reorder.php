<?php

namespace App\Actions\Client\Order\Commands\Reorder;

use App\Actions\Client\Order\Commands\CalculateOrderAmounts;
use App\Actions\Client\Order\Commands\CreateOrder;
use App\Actions\Client\Order\Commands\HandleLoyaltyPoints;
use App\Enums\PaymentMethodEnum;
use App\Events\OrderPlacedBroadcast;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Throwable;

readonly class Reorder
{
    public function __construct(
        private CreateOrder $createOrder,
        private CreateOrderItems $createOrderItems,
        private CalculateOrderAmounts $calculateOrderAmounts,
        private HandleLoyaltyPoints $handleLoyaltyPoints,
    ) {}

    /**
     * @param array $data
     * @return array|string[]
     * @throws Throwable
     */
    public function execute(array $data): array
    {
        $order = Order::query()->findOrFail($data['order_id']);
        $order->load('user', 'items.item', 'items.meat', 'items.removedIngredients');
        $user  = auth()->user();

        if (!$order || $order->items->isEmpty()) {
            return [
                'message' => 'Order is empty',
                'order' => null,
            ];
        }

        return DB::transaction(function () use ($order, $data, $user) {
            $newOrder = $this->createOrder->execute($data, $user);

            [$itemsTotalIncVat, $vatBreakdown] = $this->createOrderItems->execute($order, $newOrder);

            $isCash = $data['payment_method'] === PaymentMethodEnum::CASH->value;
            $usedPoints = $data['used_points'] ?? 0;
            $type = $data['type'];

            $amounts = $this->calculateOrderAmounts->execute(
                itemsTotalIncVat: $itemsTotalIncVat,
                vatBreakdown: $vatBreakdown,
                usedPoints: $usedPoints,
                type: $type,
            );

            $newOrder->update($amounts);

            $this->handleLoyaltyPoints->execute(
                user: $user,
                order: $newOrder,
                usedPoints: $usedPoints,
                finalTotal: $amounts['total_inc_vat'],
                isCash: $isCash,
            );

            $newOrder->load('user', 'items.item', 'items.meat', 'items.removedIngredients', 'address');

            if ($order->confirmed_at !== null) {
                event(new OrderPlacedBroadcast($newOrder));
            }

            return [
                'message' => 'Order placed successfully',
                'order' => $newOrder,
            ];
        });
    }
}
