<?php

namespace App\Http\Resources\Admin\Drink;

use App\Models\Drink;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class DrinkEditResource extends JsonResource
{
    public static $wrap = null;

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var Drink $drink */
        $drink = $this->resource;

        $locale = app()->getLocale();

        $translation = $drink->translate(
            $locale,
            false
        );

        return [
            'id' => $this->id,
            'name' => $translation?->name ?? '',
            'description' => $translation?->description ?? '',
            'category' => $this->category,
            'price' => $this->price,
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
