<?php

namespace App\Actions\Admin\Ingredient\Queries;

use App\Models\Ingredient;
use Illuminate\Support\Facades\Storage;

class GetIngredientForEdit
{
    /**
     * @param Ingredient $ingredient
     * @return array
     */
    public function execute(Ingredient $ingredient): array
    {
        $ingredient->load('allergen', 'images');

        return [
            'id' => $ingredient->id,
            'name' => $ingredient->name,
            'description' => $ingredient->description,
            'allergen' => $ingredient->allergen->id ?? null,
            'images' => $ingredient->images->map(fn($image) => [
                'id' => $image->id,
                'path' => Storage::disk('public')->url($image->path),
            ])
        ];
    }
}
