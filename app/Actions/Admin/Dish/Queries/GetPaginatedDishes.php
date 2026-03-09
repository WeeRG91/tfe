<?php

namespace App\Actions\Admin\Dish\Queries;

use App\Http\Resources\Admin\Dish\DishResource;
use App\Models\Dish;

class GetPaginatedDishes
{
    /**
     * @return array
     */
    public function execute(): array
    {
        $dishes = Dish::query()
            ->with([
                'ingredients.allergen.images',
                'images'
            ])
            ->where('deleted_at', null)
            ->orderBy('created_at', 'desc')
            ->cursorPaginate(15);

        return [
            'data' => DishResource::collection($dishes),
            'path' => $dishes->path(),
            'per_page' => $dishes->perPage(),
            'next_cursor' => $dishes->nextCursor()?->encode(),
            'next_page_url' => $dishes->nextPageUrl(),
            'prev_cursor' => $dishes->previousCursor()?->encode(),
            'prev_page_url' => $dishes->previousPageUrl(),
        ];
    }
}
