<?php

namespace App\Actions\Admin\Drink\Queries;

use App\Enums\DrinkCategoryEnum;

class GetDrinkFormData
{
    public function execute(): array
    {
        return [
            'categories' => DrinkCategoryEnum::getCategories(),
        ];
    }
}
