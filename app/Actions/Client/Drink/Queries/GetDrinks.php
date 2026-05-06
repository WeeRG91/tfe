<?php

namespace App\Actions\Client\Drink\Queries;

use App\Actions\BaseCursorPagination;
use App\Http\Resources\Client\Drink\DrinkResource;
use App\Models\Drink;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class GetDrinks extends BaseCursorPagination
{
    /**
     * @param Request $request
     * @return AnonymousResourceCollection
     */
    public function execute(Request $request): AnonymousResourceCollection
    {
        $query = Drink::query();

        $drinks = $this->filters($query, $request)->get();

        return DrinkResource::collection($drinks);
    }
}
