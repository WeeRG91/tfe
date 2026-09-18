<?php

namespace App\Actions\Client\Dish\Queries;

use App\Http\Resources\Client\Dish\DishDetailResource;
use App\Models\Dish;

class GetDish
{
    public function execute(Dish $dish): DishDetailResource
    {
        $dish->load([
            'ingredients.allergen',
            'meats',
            'ratings',
        ])
            ->loadAvg('ratings', 'rating')
            ->loadCount('ratings');

        return new DishDetailResource($dish);
    }
}
