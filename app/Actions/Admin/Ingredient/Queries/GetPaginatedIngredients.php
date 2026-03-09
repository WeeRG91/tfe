<?php

namespace App\Actions\Admin\Ingredient\Queries;

use App\Http\Resources\Admin\Ingredient\IngredientResource;
use App\Models\Ingredient;

class GetPaginatedIngredients
{
    /**
     * @return array
     */
    public function execute(): array
    {
        $ingredients = Ingredient::query()
            ->with(['allergen', 'images'])
            ->where('deleted_at', null)
            ->orderBy('created_at', 'desc')
            ->cursorPaginate(15);

        return [
            'data' => IngredientResource::collection($ingredients),
            'path' => $ingredients->path(),
            'per_page' => $ingredients->perPage(),
            'next_cursor' => $ingredients->nextCursor()?->encode(),
            'next_page_url' => $ingredients->nextPageUrl(),
            'prev_cursor' => $ingredients->previousCursor()?->encode(),
            'prev_page_url' => $ingredients->previousPageUrl(),
        ];
    }
}
