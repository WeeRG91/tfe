<?php

namespace App\Http\Resources\Admin\Order;

use App\Enums\OrderStatusEnum;
use App\Enums\OrderTypeEnum;
use App\Enums\PaymentMethodEnum;
use App\Enums\PaymentStatusEnum;
use App\Http\Resources\Client\Address\AddressResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ConfirmedOrderResource extends JsonResource
{
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
            'table_number' => $this->table_number,
            'pickup_time' => $this->pickup_time?->toISOString(),
            'pickup_name' => $this->pickup_name,
            'pickup_phone' => $this->pickup_phone,
            'delivery_address' => $this->whenLoaded('address', fn () => new AddressResource($this->address)),
            'delivery_type' => $this->delivery_type?->value,
            'delivery_company' => $this->delivery_company_name
                    ? [
                        'id' => $this->delivery_company_id,
                        'name' => $this->delivery_company_name,
                    ]
                    : null,
            'delivery_date' => $this->delivery_date?->toISOString(),
            'status' => OrderStatusEnum::getStatus($this->status),
            'payment_method' => PaymentMethodEnum::getPaymentMethod($this->payment_method),
            'payment_status' => PaymentStatusEnum::getPaymentStatus($this->payment_status),
            'confirmed_at' => $this->confirmed_at?->toISOString(),
            'paid_at' => $this->paid_at?->toISOString(),
            'prepare_at' => $this->prepare_at?->toISOString(),
            'ready_at' => $this->ready_at?->toISOString(),
            'delivered_at' => $this->delivered_at?->toISOString(),
            'completed_at' => $this->completed_at?->toISOString(),
            'cancelled_at' => $this->cancelled_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
            'notes' => $this->notes,
            'subtotal' => $this->subtotal,
            'total_inc_vat' => $this->total_inc_vat,
            'discount_total' => $this->discount_total,
            'vat_total' => $this->vat_total,
            'vat_breakdown' => $this->vat_breakdown ?? [],
            'delivery_fee' => $this->delivery_fee,
            'items' => $this->whenLoaded('items')->map(fn ($item) => new ConfirmedOrderItemResource($item)),
        ];
    }
}
