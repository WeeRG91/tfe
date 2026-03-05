<?php

namespace App\Http\Resources\Admin\Allergen;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class AllergenWithImagesResource extends JsonResource
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
            'main_image' => $this->main_image
                ? Storage::disk('public')->url($this->main_image)
                : Storage::disk('public')->url('/images/picture.png'),
        ];
    }
}
