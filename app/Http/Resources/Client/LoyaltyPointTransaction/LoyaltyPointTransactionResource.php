<?php

namespace App\Http\Resources\Client\LoyaltyPointTransaction;

use App\Enums\LoyaltyPointTransactionTypeEnum;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LoyaltyPointTransactionResource extends JsonResource
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
            'type' => LoyaltyPointTransactionTypeEnum::getStatus($this->type),
            'points' => $this->points,
            'description' => $this->description,
            'order_number' => $this->order?->order_number,
            'created_at' => $this->created_at,
        ];
    }
}
