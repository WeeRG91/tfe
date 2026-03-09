<?php

namespace App\Actions\Admin\Drink\Queries;

use App\Http\Resources\Admin\Drink\DrinkResource;
use App\Models\Drink;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class GetPaginatedDrinks
{
    /**
     * @return array
     */
    public function execute(): array
    {
        $drinks = Drink::query()
            ->with(['images'])
            ->where('deleted_at', null)
            ->orderBy('created_at', 'desc')
            ->cursorPaginate(10);

        return [
            'data' => DrinkResource::collection($drinks),
            'path' => $drinks->path(),
            'per_page' => $drinks->perPage(),
            'next_cursor' => $drinks->nextCursor()?->encode(),
            'next_page_url' => $drinks->nextPageUrl(),
            'prev_cursor' => $drinks->previousCursor()?->encode(),
            'prev_page_url' => $drinks->previousPageUrl(),
        ];
    }
}
