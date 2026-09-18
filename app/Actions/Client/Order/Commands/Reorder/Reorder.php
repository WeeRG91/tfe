<?php

namespace App\Actions\Client\Order\Commands\Reorder;

use App\Actions\Client\Order\Commands\CalculateOrderAmounts;
use App\Actions\Client\Order\Commands\CreateOrder;
use App\Actions\Client\Order\Commands\HandleLoyaltyPoints;
use App\Enums\DeliveryTypeEnum;
use App\Enums\OrderTypeEnum;
use App\Enums\PaymentMethodEnum;
use App\Events\OrderPlacedBroadcast;
use App\Models\Order;
use App\Services\RestaurantAvailabilityService;
use Illuminate\Support\Facades\DB;
use Throwable;

readonly class Reorder
{
    public function __construct(
        private CreateOrder $createOrder,
        private CreateOrderItems $createOrderItems,
        private CalculateOrderAmounts $calculateOrderAmounts,
        private HandleLoyaltyPoints $handleLoyaltyPoints,
        private RestaurantAvailabilityService $availability,
    ) {}

    /**
     * @return array|string[]
     *
     * @throws Throwable
     */
    public function execute(array $data): array
    {
        $order = Order::query()->findOrFail($data['order_id']);
        $order->load('user', 'items.item', 'items.meat', 'items.removedIngredients');
        $user = auth()->user();

        if ($order->items->isEmpty()) {
            return [
                'message' => __('messages.orders.order_empty'),
                'order' => null,
            ];
        }

        return DB::transaction(function () use ($order, $data, $user) {
            $isFutureTakeaway =
                (int) $data['type'] ===
                OrderTypeEnum::TAKEAWAY->value;

            $isCompanyDelivery =
                (int) $data['type'] ===
                OrderTypeEnum::DELIVERY->value &&
                ($data['delivery_type'] ?? null) ===
                DeliveryTypeEnum::COMPANY->value &&
                config(
                    'restaurant.delivery.company.enabled',
                    false,
                );

            if (! $isFutureTakeaway && ! $isCompanyDelivery) {
                $this->availability->assertCanAcceptOrders();
            }

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
                deliveryType: $data['delivery_type'] ?? null,
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
                'message' => __('messages.orders.placed'),
                'order' => $newOrder,
            ];
        });
    }
}
