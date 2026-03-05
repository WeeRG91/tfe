<?php

namespace App\Http\Resources\Admin\Allergen;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class AllergenEditResource extends JsonResource
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
            'description' => $this->description,
            'ingredients' => $this->ingredients->map(fn ($ingredient) => [
                'id' => $ingredient->id,
            ]),
            'main_image' => $this->main_image
                ? Storage::disk('public')->url($this->main_image)
                : null,
            'images' => $this->images->map(fn ($image) => [
                'id' => $image->id,
                'path' => Storage::disk('public')->url($image->path),
            ])
        ];
    }
}
