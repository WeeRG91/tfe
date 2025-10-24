<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class AllergenResource extends JsonResource
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
            'description' => $this->description,
            'ingredients' => IngredientWithAllergenResource::collection($this->whenLoaded('ingredients')),
            'image' => $this->images->first()
                ? Storage::disk('public')->url($this->images->first()->path)
                : Storage::disk('public')->url('images/picture.png'),
            'images' => $this->images->map(fn($image) => [
                'id' => $image->id,
                'path' => Storage::disk('public')->url($image->path),
            ]),
            'created_at' => $this->created_at->toFormattedDateString(),
            'updated_at' => $this->updated_at->toFormattedDateString(),
        ];
    }
}
