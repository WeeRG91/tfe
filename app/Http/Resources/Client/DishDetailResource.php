<?php

namespace App\Http\Resources\Client;

use App\Enums\DishCategoryEnum;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class DishDetailResource extends JsonResource
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
            'name' => $this->name,
            'main_image' => $this->main_image
                ? Storage::disk('public')->url($this->main_image)
                : Storage::disk('public')->url('/images/picture.png'),
            'description' => $this->description,
            'price' => $this->price,
            'is_available' => $this->is_available ? 'Available' : 'Unavailable',
            'category' => DishCategoryEnum::getCategory($this->category),
            'ingredients' => $this->ingredients->map(fn ($ingredient) => [
                'id' => $ingredient->id,
                'name' => $ingredient->name,
                'main_image' => $ingredient->main_image
                    ? Storage::disk('public')->url($ingredient->main_image)
                    : Storage::disk('public')->url('/images/picture.png'),
                'allergen' => $ingredient->allergen
                    ? [
                        'id' => $ingredient->allergen->id,
                        'name' => $ingredient->allergen->name,
                        'main_image' => $ingredient->allergen->main_image
                            ? Storage::disk('public')->url($ingredient->allergen->main_image)
                            : Storage::disk('public')->url('/images/picture.png'),
                    ] : null,
            ]),
        ];
    }
}
