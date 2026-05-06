<?php

namespace App\Actions\Admin\Drink\Queries;

use App\Enums\DrinkCategoryEnum;

class GetDrinkFormData
{
    /**
     * @return array
     */
    public function execute(): array
    {
        return [
            'categories' => DrinkCategoryEnum::getCategories(),
        ];
    }
}
