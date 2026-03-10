<?php

namespace App\Actions\Admin\Ingredient\Queries;

use App\Actions\Support\BaseCursorPagination;
use App\Http\Resources\Admin\Ingredient\IngredientResource;
use App\Models\Ingredient;
use Illuminate\Http\Request;

class GetPaginatedIngredients extends BaseCursorPagination
{
    /**
     * @param Request $request
     * @return array
     */
    public function execute(Request $request): array
    {
        $query = Ingredient::query()
            ->with(['allergen', 'images']);

        $query = $this->filters($query, $request);

        $ingredients = $this->paginate($query, 15);

        return $this->formatPagination($ingredients, IngredientResource::class);
    }
}
