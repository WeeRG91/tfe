<?php

namespace App\Actions\Admin\Allergen\Queries;

use App\Models\Allergen;
use App\Models\Ingredient;

class GetAllergenFormData
{
    /**
     * @return array
     */
    public function execute(): array
    {
        return [
            'ingredients' => Ingredient::all()->map(fn ($ingredient) => [
                'value' => $ingredient->id,
                'label' => $ingredient->name,
            ]),
            'allergens' => Allergen::all()->map(fn ($allergen) => [
                'value' => $allergen->id,
                'label' => $allergen->name,
            ]),
        ];
    }
}
