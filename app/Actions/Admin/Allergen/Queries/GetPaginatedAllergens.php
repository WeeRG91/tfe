<?php

namespace App\Actions\Admin\Allergen\Queries;

use App\Http\Resources\Admin\Allergen\AllergenResource;
use App\Models\Allergen;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class GetPaginatedAllergens
{
    /**
     * @return AnonymousResourceCollection
     */
    public function execute(): AnonymousResourceCollection
    {
        $allergens = Allergen::query()
            ->with(['ingredients', 'images'])
            ->where('deleted_at', null)
            ->orderBy('name')
            ->paginate(10);

        return AllergenResource::collection($allergens);
    }
}
