<?php

namespace App\Http\Resources\Api\V1;

use App\Models\Meat;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Meat */
class MeatResource extends JsonResource
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
            'name' => $this->name,
            'extra_price' => number_format(
                (float) $this->extra_price, 2, '.', ''
            ),
        ];
    }
}
