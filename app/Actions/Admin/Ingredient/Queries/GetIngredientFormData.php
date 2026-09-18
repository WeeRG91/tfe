<?php

namespace App\Actions\Admin\Ingredient\Queries;

use App\Models\Allergen;

class GetIngredientFormData
{
    public function execute(): array
    {
        return [
            'allergens' => Allergen::all()->map(fn ($allergen) => [
                'value' => $allergen->id,
                'label' => $allergen->name,
            ]),
        ];
    }
}
