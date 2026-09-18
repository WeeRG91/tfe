<?php

namespace App\Actions\Admin\Dish\Queries;

use App\Enums\DishCategoryEnum;
use App\Models\Allergen;
use App\Models\Ingredient;
use App\Models\Meat;

class GetDishFormData
{
    public function execute(): array
    {
        return [
            'ingredients' => Ingredient::query()
                ->get()
                ->map(fn (Ingredient $ingredient) => [
                    'value' => $ingredient->id,
                    'label' => $ingredient->name,
                ]),
            'meats' => Meat::query()
                ->get()
                ->map(fn (Meat $meat) => [
                    'value' => $meat->id,
                    'label' => $meat->name,
                ]),
            'allergens' => Allergen::query()
                ->get()
                ->map(fn ($allergen) => [
                    'value' => $allergen->id,
                    'label' => $allergen->name,
                ]),
            'categories' => DishCategoryEnum::getCategories(),
        ];
    }
}
