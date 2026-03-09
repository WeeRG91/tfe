<?php

namespace App\Actions\Admin\Meat\Queries;

use App\Http\Resources\Admin\Meat\MeatResource;
use App\Models\Meat;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class GetPaginatedMeats
{
    /**
     * @return array
     */
    public function execute(): array
    {
        $meats = Meat::query()
            ->with('images')
            ->orderByDesc('created_at')
            ->cursorPaginate(10);

        return [
            'data' => MeatResource::collection($meats),
            'path' => $meats->path(),
            'per_page' => $meats->perPage(),
            'next_cursor' => $meats->nextCursor()?->encode(),
            'next_page_url' => $meats->nextPageUrl(),
            'prev_cursor' => $meats->previousCursor()?->encode(),
            'prev_page_url' => $meats->previousPageUrl(),
        ];
    }
}
