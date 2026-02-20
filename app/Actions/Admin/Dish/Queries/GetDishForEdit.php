<?php

namespace App\Actions\Admin\Dish\Queries;

use App\Models\Dish;
use Illuminate\Support\Facades\Storage;

class GetDishForEdit
{
    /**
     * @param Dish $dish
     * @return array
     */
    public function execute(Dish $dish): array
    {
        $dish->load(['ingredients', 'images']);

        return [
          'id' => $dish->id,
          'name' => $dish->name,
          'category' => $dish->category,
          'description' => $dish->description,
          'price' => $dish->price,
          'ingredients' => $dish->ingredients->map(fn ($ingredient) => [
              'id' => $ingredient->id,
          ]),
          'images' => $dish->images->map(fn ($image) => [
              'id' => $image->id,
              'path' => Storage::disk('public')->url($image->path),
          ]),
        ];
    }
}
