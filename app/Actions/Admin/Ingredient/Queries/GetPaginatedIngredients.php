<?php

namespace App\Actions\Admin\Ingredient\Queries;

use App\Http\Resources\Admin\Ingredient\IngredientResource;
use App\Models\Ingredient;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class GetPaginatedIngredients
{
    /**
     * @return AnonymousResourceCollection
     */
    public function execute(): AnonymousResourceCollection
    {
        $ingredients = Ingredient::query()
            ->with(['allergen', 'images'])
            ->where('deleted_at', null)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return IngredientResource::collection($ingredients);
    }
}
