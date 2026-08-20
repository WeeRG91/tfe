<?php

namespace App\Http\Resources\Api\V1;

use App\Models\CartItem;
use App\Models\Dish;
use App\Models\Drink;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/** @mixin CartItem */
class CartItemResource extends JsonResource
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
            'item_type' => $this->itemType(),
            'item' => [
                'id' => $this->item->id,
                'name' => $this->item->name,
                'image_url' => $this->item->main_image
                    ? Storage::disk('public')->url($this->item->main_image)
                    : null,
            ],
            'quantity' => (int) $this->quantity,
            'unit_price' => $this->money($this->unit_price),
            'line_total' => $this->money($this->total),
            'spicy_level' => $this->spicy_level !== null
                ? (int) $this->spicy_level
                : null,
            'notes' => $this->notes,
            'meat' => $this->whenLoaded(
                'meat',
                fn () => $this->meat
                    ? new MeatResource($this->meat)
                    : null,
            ),
            'removed_ingredients' => IngredientResource::collection(
                $this->whenLoaded('removedIngredients')
            ),
        ];
    }

    private function itemType(): string
    {
        return match ($this->item_type) {
            Dish::class => 'dish',
            Drink::class => 'drink',
            default => 'unknown',
        };
    }

    private function money(float $value): string
    {
        return number_format($value, 2, '.', '');
    }
}
