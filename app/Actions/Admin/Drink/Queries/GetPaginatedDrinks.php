<?php

namespace App\Actions\Admin\Drink\Queries;

use App\Actions\BaseCursorPagination;
use App\Http\Resources\Admin\Drink\DrinkResource;
use App\Models\Drink;
use Illuminate\Http\Request;

class GetPaginatedDrinks extends BaseCursorPagination
{
    public function execute(Request $request): array
    {
        $query = Drink::query()
            ->with(['images']);

        $query = $this->filters($query, $request);

        $drinks = $this->paginate($query, 15);

        return $this->formatPagination($drinks, DrinkResource::class);
    }
}
