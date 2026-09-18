<?php

namespace App\Actions\Client\Order\Commands\PlaceOrder;

use App\Actions\Client\Order\Commands\CalculateOrderAmounts;
use App\Actions\Client\Order\Commands\CreateOrder;
use App\Actions\Client\Order\Commands\HandleLoyaltyPoints;
use App\Enums\DeliveryTypeEnum;
use App\Enums\OrderTypeEnum;
use App\Enums\PaymentMethodEnum;
use App\Events\OrderPlacedBroadcast;
use App\Services\RestaurantAvailabilityService;
use Illuminate\Support\Facades\DB;
use Throwable;

readonly class PlaceOrder
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
        $user = auth()->user();

        return DB::transaction(function () use ($data, $user) {
            $isFutureTakeaway = (int) $data['type'] === OrderTypeEnum::TAKEAWAY->value;

            $isCompanyDelivery =
                (int) $data['type'] === OrderTypeEnum::DELIVERY->value &&
                ($data['delivery_type'] ?? null) === DeliveryTypeEnum::COMPANY->value &&
                config(
                    'restaurant.delivery.company.enabled',
                    false,
                );

            if (! $isFutureTakeaway && ! $isCompanyDelivery) {
                $this->availability
                    ->assertCanAcceptOrders();
            }

            $cart = $user->cart()
                ->whereKey($data['cart_id'])
                ->lockForUpdate()
                ->firstOrFail();

            $cart->load('items.item', 'items.meat', 'items.removedIngredients');

            if (! $cart || $cart->items->isEmpty()) {
                return [
                    'message' => __('messages.orders.cart_empty'),
                    'order' => null,
                ];
            }

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
                deliveryType: $data['delivery_type'] ?? null,
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
                'message' => __('messages.orders.placed'),
                'order' => $order,
            ];
        });
    }
}
