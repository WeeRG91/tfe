<?php

namespace App\Actions\Admin\Allergen\Queries;

use App\Http\Resources\Admin\Allergen\AllergenResource;
use App\Models\Allergen;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class GetPaginatedAllergens
{
    /**
     * @return array
     */
    public function execute(): array
    {
        $allergens = Allergen::query()
            ->with(['ingredients', 'images'])
            ->where('deleted_at', null)
            ->orderBy('name')
            ->cursorPaginate(15);

        return [
            'data' => AllergenResource::collection($allergens),
            'path' => $allergens->path(),
            'per_page' => $allergens->perPage(),
            'next_cursor' => $allergens->nextCursor()?->encode(),
            'next_page_url' => $allergens->nextPageUrl(),
            'prev_cursor' => $allergens->previousCursor()?->encode(),
            'prev_page_url' => $allergens->previousPageUrl(),
        ];
    }
}
