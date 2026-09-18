<?php

namespace App\Actions\Admin\Dish\Queries;

use App\Http\Resources\Admin\Dish\DishEditResource;
use App\Models\Dish;

class GetDishForEdit
{
    public function execute(Dish $dish): DishEditResource
    {
        $dish->load([
            'translations',
            'ingredients',
            'meats',
            'images',
        ]);

        return new DishEditResource($dish);
    }
}
