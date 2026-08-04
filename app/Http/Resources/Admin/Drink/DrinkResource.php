<?php

namespace App\Http\Resources\Admin\Drink;

use App\Enums\DrinkCategoryEnum;
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
            'main_image' => $this->main_image
                ? Storage::disk('public')->url($this->main_image)
                : Storage::disk('public')->url('/images/picture.png'),
            'description' => $this->description,
            'price' => $this->price,
            'is_available' => $this->is_available ? 'available' : 'unavailable',
            'category' => DrinkCategoryEnum::getCategory($this->category),
            'created_at' => $this->created_at?->toDateString(),
            'updated_at' => $this->updated_at?->toDateString(),
            'deleted_at' => $this->deleted_at?->toDateString(),
        ];
    }
}
