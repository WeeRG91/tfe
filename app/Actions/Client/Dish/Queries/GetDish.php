<?php

namespace App\Actions\Client\Dish\Queries;

use App\Http\Resources\Client\Dish\DishDetailResource;
use App\Models\Dish;

class GetDish
{
    /**
     * @param Dish $dish
     * @return DishDetailResource
     */
    public function execute(Dish $dish): DishDetailResource
    {
        $dish->load(['ingredients.allergen', 'meats']);

        return new DishDetailResource($dish);
    }
}
