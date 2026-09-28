<?php

namespace App\Actions\Client\Dish\Queries;

use App\Http\Resources\Client\Dish\DishResource;
use App\Models\Dish;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class GetRecommendedDishes
{
    public function execute(int $limit = 3): AnonymousResourceCollection
    {
        $dishes = Dish::query()
            ->where('is_available', true)
            ->with(['ingredients.allergen', 'meats'])
            ->withAvg('ratings', 'rating')
            ->withCount('ratings')
            ->inRandomOrder()
            ->limit($limit)
            ->get();

        return DishResource::collection($dishes);
    }
}
