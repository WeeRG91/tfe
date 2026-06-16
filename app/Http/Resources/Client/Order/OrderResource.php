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
                'loyalty_points' => $this->user->loyalty_points,
            ]),
            'type' => OrderTypeEnum::getType($this->type),
            'table_number' => $this->table_number,
            'pickup_time' => $this->pickup_time?->format('Y-m-d H:i'),
            'pickup_name' => $this->pickup_name,
            'pickup_phone' => $this->pickup_phone,
            'delivery_address' => $this->whenLoaded('address', fn () => new AddressResource($this->address)),
            'status' => OrderStatusEnum::getStatus($this->status),
            'payment_method' => PaymentMethodEnum::getPaymentMethod($this->payment_method),
            'payment_status' => PaymentStatusEnum::getPaymentStatus($this->payment_status),
            'confirmed_at' => $this->confirmed_at?->format('Y-m-d H:i'),
            'paid_at' => $this->paid_at?->format('Y-m-d H:i:s'),
            'prepare_at' => $this->prepare_at?->format('Y-m-d H:i'),
            'ready_at' => $this->ready_at?->format('Y-m-d H:i'),
            'delivered_at' => $this->delivered_at?->format('Y-m-d H:i'),
            'completed_at' => $this->completed_at?->format('Y-m-d H:i'),
            'cancelled_at' => $this->cancelled_at?->format('Y-m-d H:i'),
            'created_at' => $this->created_at?->format('Y-m-d H:i'),
            'notes' => $this->notes,
            'subtotal' => $this->subtotal,
            'total_inc_vat' => $this->total_inc_vat,
            'discount_total' => $this->discount_total,
            'vat_total' => $this->vat_total,
            'vat_breakdown' => $this->vat_breakdown ?? [],
            'delivery_fee' => $this->delivery_fee,
            'items' => $this->whenLoaded('items')->map(fn ($item) => new OrderItemResource($item)),
        ];
    }
}
