<?php

namespace App\Actions\Admin\Ingredient\Queries;

use App\Http\Resources\Admin\Ingredient\IngredientEditResource;
use App\Models\Ingredient;

class GetIngredientForEdit
{
    public function execute(Ingredient $ingredient): IngredientEditResource
    {
        $ingredient->load([
            'translations',
            'allergen',
            'images',
        ]);

        return new IngredientEditResource($ingredient);
    }
}
