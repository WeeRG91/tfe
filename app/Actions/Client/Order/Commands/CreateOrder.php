<?php

namespace App\Actions\Client\Order\Commands;

use App\Enums\OrderStatusEnum;
use App\Enums\PaymentMethodEnum;
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

        return Order::query()->create([
            'user_id' => $user->id,
            'order_number' => 'ORD-'.Str::ulid(),
            'type' => $data['type'],
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
