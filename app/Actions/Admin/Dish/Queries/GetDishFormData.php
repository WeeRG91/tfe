<?php

namespace App\Actions\Admin\Dish\Queries;

use App\Enums\DishCategoryEnum;
use App\Models\Ingredient;

class GetDishFormData
{
    /**
     * @return array
     */
    public function execute(): array
    {
        return [
            'ingredients' => Ingredient::all()->map(fn (Ingredient $ingredient) => [
                'value' => $ingredient->id,
                'label' => $ingredient->name,
            ]),
            'categories' => DishCategoryEnum::getCategories(),
        ];
    }
}
