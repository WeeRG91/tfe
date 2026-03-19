<?php

namespace App\Actions\Support;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Pagination\CursorPaginator;

abstract class BaseCursorPagination
{
    /**
     * @param Builder $query
     * @param Request $request
     * @return Builder
     */
    protected function filters(Builder $query, Request $request): Builder
    {
        if (!$request->filter || $request->filter === 'all') {
            $query->withoutTrashed();
        }

        $query->when($request->filter === 'available', fn ($q) =>
            $q->where('is_available', true)->withoutTrashed()
        );

        $query->when($request->filter === 'unavailable', fn ($q) =>
            $q->where('is_available', false)->withoutTrashed()
        );

        $query->when($request->filter === 'deleted', fn ($q) =>
            $q->onlyTrashed()
        );

        $query->when($request->category, fn ($q) =>
            $q->where('category', $request->category)
        );

        $query->when($request->allergen, fn ($q) =>
            $q->where('allergen_id', $request->allergen)
        );

        $query->when($request->search, fn ($q) =>
            $q->where('name', 'LIKE', "$request->search%")
        );

        return $query;
    }

    /**
     * @param Builder $query
     * @param int $perPage
     * @return CursorPaginator
     */
    protected function paginate(Builder $query, int $perPage): CursorPaginator
    {
        return $query
            ->orderBy('updated_at', 'desc')
            ->cursorPaginate($perPage);
    }

    /**
     * @param CursorPaginator $items
     * @param class-string<JsonResource> $resource
     * @return array
     */
    protected function formatPagination(CursorPaginator $items, string $resource): array
    {
        return [
            'data' => $resource::collection($items),
            'path' => $items->path(),
            'per_page' => $items->perPage(),
            'next_cursor' => $items->nextCursor()?->encode(),
            'next_page_url' => $items->nextPageUrl(),
            'prev_cursor' => $items->previousCursor()?->encode(),
            'prev_page_url' => $items->previousPageUrl(),
        ];
    }
}
