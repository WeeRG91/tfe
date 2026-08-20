<?php

namespace App\Http\Resources\Api\V1;

use App\Enums\OrderStatusEnum;
use App\Enums\OrderTypeEnum;
use App\Enums\PaymentMethodEnum;
use App\Enums\PaymentStatusEnum;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
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
            'user' => $this->whenLoaded(
                'user',
                fn () => new UserResource($this->user),
            ),
            'type' => $this->enumData($this->type),
            'table_number' => $this->table_number,
            'pickup_time' => $this->pickup_time?->toISOString(),
            'pickup_name' => $this->pickup_name,
            'pickup_phone' => $this->pickup_phone,
            'delivery_address' => $this->whenLoaded(
                'address',
                fn () => $this->address
                    ? new AddressResource($this->address)
                    : null,
            ),
            'status' => $this->enumData($this->status),
            'payment_method' => $this->enumData($this->payment_method),
            'payment_status' => $this->enumData($this->payment_status),
            'confirmed_at' => $this->confirmed_at?->toISOString(),
            'notes' => $this->notes,
            'subtotal' => $this->money($this->subtotal),
            'discount_total' => $this->money($this->discount_total),
            'vat_total' => $this->money($this->vat_total),
            'vat_breakdown' => collect($this->vat_breakdown ?? [])
                ->map(fn (array $row): array => [
                    'vat_rate' => (int) $row['vat_rate'],
                    'vat_total' => $this->money($row['vat_total']),
                    'total_inc_vat' => $this->money($row['total_inc_vat']),
                ])
                ->values(),
            'delivery_fee' => $this->money($this->delivery_fee),
            'total' => $this->money($this->total_inc_vat),
            'items' => OrderItemResource::collection($this->whenLoaded('items')),
        ];
    }

    private function enumData(
        OrderTypeEnum|OrderStatusEnum|PaymentMethodEnum|PaymentStatusEnum $enum,
    ): array
    {
        return [
            'value' => $enum->value,
            'key' => $enum->key(),
            'label' => $enum->translatedLabel(),
        ];
    }

    private function money(float $value): string
    {
        return number_format($value, 2, '.', '');
    }
}
