<?php

namespace App\Http\Resources\Client\LoyaltyPointTransaction;

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
            'type' => $this->type->label(),
            'points' => $this->points,
            'description' => $this->description,
            'created_at' => $this->created_at,
        ];
    }
}
