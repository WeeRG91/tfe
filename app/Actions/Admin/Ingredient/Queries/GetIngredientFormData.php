<?php

namespace App\Actions\Admin\Ingredient\Queries;

use App\Http\Resources\Admin\IngredientResource;
use App\Models\Allergen;

class GetIngredientFormData
{
    /**
     * @return array
     */
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
