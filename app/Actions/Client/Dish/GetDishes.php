<?php

namespace App\Actions\Client\Dish;

use App\Actions\BaseCursorPagination;
use App\Http\Resources\Client\DishResource;
use App\Models\Dish;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class GetDishes extends BaseCursorPagination
{
    /**
     * @param Request $request
     * @return AnonymousResourceCollection
     */
    public function execute(Request $request): AnonymousResourceCollection
    {
        sleep(1);
        $query = Dish::query()
            ->with([
                'ingredients.allergen.images',
                'images'
            ]);

        $dishes = $this->filters($query, $request)->get();

        return DishResource::collection($dishes);
    }
}
