<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LoyaltyPointTransactionResource extends JsonResource
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
            'type' => [
                'value' => $this->type->value,
                'key' => $this->type->key(),
                'label' => $this->type->label(),
            ],
            'points' => $this->points,
            'description' => $this->description,
            'order_number' => $this->order?->order_number,
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
