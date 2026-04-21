<?php

namespace App\Actions\Client\Dish;

use App\Http\Resources\Client\DishDetailResource;
use App\Models\Dish;

class GetDish
{
    /**
     * @param Dish $dish
     * @return DishDetailResource
     */
    public function execute(Dish $dish): DishDetailResource
    {
        $dish->load(['ingredients.allergen']);

        return new DishDetailResource($dish);
    }
}
