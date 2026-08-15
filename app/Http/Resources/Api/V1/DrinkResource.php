<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class DrinkResource extends JsonResource
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
            'price' => number_format((float) $this->price, 2, '.', ''),
            'image_url' => $this->main_image
                ? Storage::disk('public')->url($this->main_image)
                : null,
            'is_available' => (bool) $this->is_available,
            'category' => [
                'value' => $this->category->value,
                'key' => $this->category->key(),
            ],
        ];
    }
}
