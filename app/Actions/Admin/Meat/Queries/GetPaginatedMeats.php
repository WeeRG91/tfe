<?php

namespace App\Actions\Admin\Meat\Queries;

use App\Actions\BaseCursorPagination;
use App\Http\Resources\Admin\Meat\MeatResource;
use App\Models\Meat;
use Illuminate\Http\Request;

class GetPaginatedMeats extends BaseCursorPagination
{
    public function execute(Request $request): array
    {
        $query = Meat::query()
            ->with('images');

        $query = $this->filters($query, $request);

        $meats = $this->paginate($query, 15);

        return $this->formatPagination($meats, MeatResource::class);
    }
}
