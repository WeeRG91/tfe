<?php

namespace App\Http\Resources\Admin\Ingredient;

use App\Http\Resources\Admin\Allergen\AllergenWithImagesResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class IngredientWithAllergenResource extends JsonResource
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
            'allergen' => new AllergenWithImagesResource($this->whenLoaded('allergen')),
        ];
    }
}
