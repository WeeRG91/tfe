<?php

namespace App\Actions\Admin\Dish\Queries;

use App\Models\Dish;
use Illuminate\Pagination\LengthAwarePaginator;
use LaravelIdea\Helper\App\Models\_IH_Dish_C;

class GetPaginatedDishes
{
    /**
     * @return array|LengthAwarePaginator|_IH_Dish_C
     */
    public function execute(): array|LengthAwarePaginator|_IH_Dish_C
    {
        return Dish::query()
            ->with([
                'ingredients.allergen.images',
                'images'
            ])
            ->where('deleted_at', null)
            ->orderBy('created_at', 'desc')
            ->paginate(10);
    }
}
