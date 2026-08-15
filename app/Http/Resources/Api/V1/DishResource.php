<?php

namespace App\Http\Resources\Api\V1;

use App\Models\Dish;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/** @mixin Dish */
class DishResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $hasRatingSummary = array_key_exists(
            'ratings_count',
            $this->resource->getAttributes(),
        );

        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'price' => number_format((float) $this->price, 2, '.', ''),
            'image_url' => $this->main_image
                ? Storage::disk('public')->url($this->main_image)
                : null,
            'is_available' => (bool) $this->is_available,
            'category' => [
                'value' => $this->category->value,
                'key' => $this->category->key(),
            ],
            'default_spicy_level' => (int) $this->default_spicy_level,
            'ingredients' => IngredientResource::collection(
                $this->whenLoaded('ingredients'),
            ),
            'meats' => MeatResource::collection(
                $this->whenLoaded('meats'),
            ),
            'rating' => $this->when(
                $hasRatingSummary,
                fn () => [
                    'average' => round(
                        (float) ($this->ratings_avg_rating ?? 0),
                        1,
                    ),
                    'count' => (int) $this->ratings_count,
                ],
            ),
        ];
    }
}
