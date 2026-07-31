<?php

namespace App\Http\Resources\Admin\Dish;

use App\Models\Dish;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class DishEditResource extends JsonResource
{
    public static $wrap = null;

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var Dish $dish */
        $dish = $this->resource;

        $locale = app()->getLocale();

        $translation = $dish->translate(
            $locale,
            false
        );

        return [
            'id' => $this->id,
            'name' => $translation?->name ?? '',
            'description' => $translation?->description ?? '',
            'category' => $this->category,
            'price' => $this->price,
            'default_spicy_level' => $this->default_spicy_level,
            'meats' =>$this->meats->map(fn ($meat) => [
                'id' => $meat->id,
            ]),
            'ingredients' => $this->ingredients->map(fn ($ingredient) => [
                'id' => $ingredient->id,
            ]),
            'main_image' => $this->main_image
                ? Storage::disk('public')->url($this->main_image)
                : null,
            'images' => $this->images->map(fn ($image) => [
                'id' => $image->id,
                'path' => Storage::disk('public')->url($image->path),
            ]),
        ];
    }
}
