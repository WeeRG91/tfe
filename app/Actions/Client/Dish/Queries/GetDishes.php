<?php

namespace App\Actions\Client\Dish\Queries;

use App\Actions\BaseCursorPagination;
use App\Http\Resources\Client\Dish\DishResource;
use App\Models\Dish;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class GetDishes extends BaseCursorPagination
{
    public function execute(Request $request): AnonymousResourceCollection
    {
        $query = Dish::query()
            ->with([
                'ingredients.allergen',
                'meats',
                'ratings',
            ])
            ->withAvg('ratings', 'rating')
            ->withCount('ratings');

        $dishes = $this->filters($query, $request)->get();

        return DishResource::collection($dishes);
    }
}
