<?php

namespace App\Actions\Admin\Dish\Queries;

use App\Http\Resources\Admin\Dish\DishEditResource;
use App\Models\Dish;

class GetDishForEdit
{
    /**
     * @param Dish $dish
     * @return DishEditResource
     */
    public function execute(Dish $dish): DishEditResource
    {
        $dish->load(['ingredients', 'images']);

        return new DishEditResource($dish);
    }
}
