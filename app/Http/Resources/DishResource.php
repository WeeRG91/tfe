<?php

namespace App\Http\Resources;

use App\Enums\DishCategoryEnum;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class DishResource extends JsonResource
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
            'image' => $this->images->first()
                ? Storage::disk('public')->url($this->images->first()->path)
                : Storage::disk('public')->url('/images/picture.png'),
            'images' => $this->images->map(fn ($image) => [
                'id' => $image->id,
                'path' => Storage::disk('public')->url($image->path),
            ]),
            'description' => $this->description,
            'price' => $this->price,
            'is_available' => $this->is_available ? 'Available' : 'Unavailable',
            'ingredients' => IngredientWithAllergenResource::collection($this->whenLoaded('ingredients')),
            'category' => DishCategoryEnum::getCategory($this->category),
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
        ];
    }
}
