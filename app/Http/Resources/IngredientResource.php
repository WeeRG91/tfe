<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

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
            'description' => $this->description,
            'image' => $this->images->first()
                ? Storage::disk('public')->url($this->images->first()->path)
                : Storage::disk('public')->url('images/picture.png'),
            'images' => $this->images->map(fn($image) => [
                'id' => $image->id,
                'path' => Storage::disk('public')->url($image->path),
            ]),
            'allergen' => new AllergenWithImagesResource($this->whenLoaded('allergen')),
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
        ];
    }
}
