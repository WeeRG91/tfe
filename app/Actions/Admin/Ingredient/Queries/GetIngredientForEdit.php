<?php

namespace App\Actions\Admin\Ingredient\Queries;

use App\Http\Resources\Admin\Ingredient\IngredientEditResource;
use App\Models\Ingredient;

class GetIngredientForEdit
{
    /**
     * @param Ingredient $ingredient
     * @return IngredientEditResource
     */
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
