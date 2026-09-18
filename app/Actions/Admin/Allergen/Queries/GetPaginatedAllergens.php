<?php

namespace App\Actions\Admin\Allergen\Queries;

use App\Actions\BaseCursorPagination;
use App\Http\Resources\Admin\Allergen\AllergenResource;
use App\Models\Allergen;
use Illuminate\Http\Request;

class GetPaginatedAllergens extends BaseCursorPagination
{
    public function execute(Request $request): array
    {
        $query = Allergen::query()
            ->with(['ingredients', 'images']);

        $query = $this->filters($query, $request);

        $allergens = $this->paginate($query, 15);

        return $this->formatPagination($allergens, AllergenResource::class);
    }
}
