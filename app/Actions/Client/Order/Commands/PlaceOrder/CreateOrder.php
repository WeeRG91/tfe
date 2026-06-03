<?php

namespace App\Actions\Client\Order\Commands\PlaceOrder;

use App\Enums\OrderStatusEnum;
use App\Enums\PaymentMethodEnum;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class CreateOrder
{
    /**
     * @param array $data
     * @param User $user
     * @return Order|Model
     */
    public function execute(array $data, User $user): Order|Model
    {
        $isCash = $data['payment_method'] === PaymentMethodEnum::CASH->value;

        return Order::query()->create([
            'user_id' => $user->id,
            'order_number' => 'ORD-' . now()->format('Ymd') . '-' . rand(1000, 9999),
            'type' => $data['type'],
            'table_number' => $data['table_number'] ?? null,
            'pickup_time' => $data['pickup_time'] ?? null,
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
}
