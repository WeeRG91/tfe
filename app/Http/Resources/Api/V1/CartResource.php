<?php

namespace App\Http\Resources\Api\V1;

use App\Models\Cart;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Cart */
class CartResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $items = $this->relationLoaded('items')
            ? $this->items
            : collect();

        return [
            'id' => $this->id,
            'items' => CartItemResource::collection($items),
            'summary' => [
                'quantity' => (int) $items->sum('quantity'),
                'total' => number_format(
                    (float) $items->sum('total'),
                    2,
                    '.',
                    ''
                ),
            ],
        ];
    }
}
