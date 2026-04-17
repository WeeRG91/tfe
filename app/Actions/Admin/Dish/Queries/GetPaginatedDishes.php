<?php

namespace App\Actions\Admin\Dish\Queries;

use App\Actions\BaseCursorPagination;
use App\Http\Resources\Admin\Dish\DishResource;
use App\Models\Dish;
use Illuminate\Http\Request;

class GetPaginatedDishes extends BaseCursorPagination
{
    /**
     * @param Request $request
     * @return array
     */
    public function execute(Request $request): array
    {
        $query = Dish::query()
            ->with(['images']);

        $query = $this->filters($query, $request);

        $dishes = $this->paginate($query, 15);

        return $this->formatPagination($dishes, DishResource::class);
    }
}
