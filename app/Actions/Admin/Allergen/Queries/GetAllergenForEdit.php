<?php

namespace App\Actions\Admin\Allergen\Queries;

use App\Models\Allergen;
use Illuminate\Support\Facades\Storage;

class GetAllergenForEdit
{
    /**
     * @param Allergen $allergen
     * @return array
     */
    public function execute(Allergen $allergen): array
    {
        $allergen->load(['ingredients', 'images']);

        return [
            'id' => $allergen->id,
            'name' => $allergen->name,
            'description' => $allergen->description,
            'ingredients' => $allergen->ingredients->map(fn ($ingredient) => [
                'id' => $ingredient->id,
            ]),
            'images' => $allergen->images->map(fn ($image) => [
                'id' => $image->id,
                'path' => Storage::disk('public')->url($image->path),
            ]),
        ];
    }
}
