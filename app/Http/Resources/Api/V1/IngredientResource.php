<?php

namespace App\Http\Resources\Api\V1;

use App\Models\Ingredient;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/** @mixin Ingredient */
class IngredientResource extends JsonResource
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
            'image_url' => $this->main_image
                ? Storage::disk('public')->url($this->main_image)
                : null,
            'allergen' => $this->whenLoaded(
                'allergen',
                fn () => $this->allergen
                    ? new AllergenResource($this->allergen)
                    : null,
            ),
        ];
    }
}
