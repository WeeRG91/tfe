<?php

namespace App\Actions\Admin\Dish\Queries;

use App\Http\Resources\Admin\Dish\DishResource;
use App\Models\Dish;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class GetPaginatedDishes
{
    /**
     * @return AnonymousResourceCollection
     */
    public function execute(): AnonymousResourceCollection
    {
        $dishes = Dish::query()
            ->with([
                'ingredients.allergen.images',
                'images'
            ])
            ->where('deleted_at', null)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return DishResource::collection($dishes);
    }
}
