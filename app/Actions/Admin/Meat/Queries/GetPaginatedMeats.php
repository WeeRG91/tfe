<?php

namespace App\Actions\Admin\Meat\Queries;

use App\Http\Resources\MeatResource;
use App\Models\Meat;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class GetPaginatedMeats
{
    /**
     * @return AnonymousResourceCollection
     */
    public function execute(): AnonymousResourceCollection
    {
        $meats = Meat::query()
            ->with('images')
            ->orderByDesc('created_at')
            ->paginate(10);

        return MeatResource::collection($meats);
    }
}
