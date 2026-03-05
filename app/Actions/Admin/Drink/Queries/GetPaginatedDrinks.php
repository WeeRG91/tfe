<?php

namespace App\Actions\Admin\Drink\Queries;

use App\Http\Resources\Admin\Drink\DrinkResource;
use App\Models\Drink;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class GetPaginatedDrinks
{
    /**
     * @return AnonymousResourceCollection
     */
    public function execute(): AnonymousResourceCollection
    {
        $drinks = Drink::query()
            ->with(['images'])
            ->where('deleted_at', null)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return DrinkResource::collection($drinks);
    }
}
