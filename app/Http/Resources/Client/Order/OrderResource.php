<?php

namespace App\Http\Resources\Client\Order;

use App\Enums\OrderStatusEnum;
use App\Enums\OrderTypeEnum;
use App\Enums\PaymentMethodEnum;
use App\Enums\PaymentStatusEnum;
use App\Http\Resources\Client\Address\AddressResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public static $wrap = null;

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_number' => $this->order_number,
            'user' => $this->whenLoaded('user', fn () => [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'email' => $this->user->email,
            ]),
            'type' => OrderTypeEnum::getType($this->type),
            'table_number' => $this->table,
            'pickup_time' => $this->pickup_time?->format('Y-m-d H:i'),
            'pickup_name' => $this->pickup_name,
            'pickup_phone' => $this->phone,
            'delivery_address' => $this->whenLoaded('address', fn () => new AddressResource($this->address)),
            'status' => OrderStatusEnum::getStatus($this->status),
            'payment_method' => PaymentMethodEnum::getPaymentMethod($this->payment_method),
            'payment_status' => PaymentStatusEnum::getPaymentStatus($this->payment_status),
            'confirmed_at' => $this->confirmed_at?->format('Y-m-d H:i:s'),
            'paid_at' => $this->paid_at?->format('Y-m-d H:i:s'),
            'delivered_at' => $this->delivered_at?->format('Y-m-d H:i:s'),
            'completed_at' => $this->completed_at?->format('Y-m-d H:i:s'),
            'cancelled_at' => $this->cancelled_at?->format('Y-m-d H:i:s'),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'notes' => $this->notes,
            'total_price' => $this->total_price,
            'items' => $this->whenLoaded('items')->map(fn ($item) => new OrderItemResource($item)),
        ];
    }
}
