<?php

namespace App\Actions\Client\Order\Commands;

use App\Enums\DeliveryTypeEnum;
use App\Enums\OrderStatusEnum;
use App\Enums\PaymentMethodEnum;
use App\Models\DeliveryCompany;
use App\Models\Order;
use App\Models\User;
use App\Services\RestaurantAvailabilityService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

readonly class CreateOrder
{
    public function __construct(
        private RestaurantAvailabilityService $availability,
    ) {}

    public function execute(array $data, User $user): Order|Model
    {
        $isCash = $data['payment_method'] === PaymentMethodEnum::CASH->value;

        $deliveryCompany = null;

        if (
            ($data['delivery_type'] ?? null) === DeliveryTypeEnum::COMPANY->value
        ) {
            $deliveryCompany = DeliveryCompany::query()
                ->where('is_active', true)
                ->findOrFail(
                    $data['delivery_company_id'],
                );
        }

        return Order::query()->create([
            'user_id' => $user->id,
            'order_number' => 'ORD-'.Str::ulid(),
            'type' => $data['type'],
            'delivery_type' => $data['delivery_type'] ?? null,
            'delivery_company_id' => $deliveryCompany?->id,
            'delivery_company_name' => $deliveryCompany?->name,
            'delivery_date' => $deliveryCompany
                ? $data['delivery_date']
                : null,
            'table_number' => $data['table_number'] ?? null,
            'pickup_time' => $this->normalizePickupTime($data),
            'pickup_name' => $data['pickup_name'] ?? null,
            'pickup_phone' => $data['pickup_phone'] ?? null,
            'address_id' => $data['address_id'] ?? null,
            'payment_method' => $data['payment_method'],
            'payment_status' => OrderStatusEnum::PENDING->value,
            'status' => $isCash
                ? OrderStatusEnum::CONFIRMED->value
                : OrderStatusEnum::PENDING->value,
            'confirmed_at' => $isCash ? now() : null,
            'notes' => $data['notes'] ?? null,
        ]);
    }

    private function normalizePickupTime(array $data): ?string
    {
        $pickupTime = $data['pickup_time'] ?? null;

        if (! is_string($pickupTime) || $pickupTime === '') {
            return null;
        }

        return $this->availability
            ->parsePickupTime($pickupTime)
            ->utc()
            ->format('Y-m-d H:i:s');
    }
}
