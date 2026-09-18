<?php

namespace App\Actions;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Pagination\CursorPaginator;

abstract class BaseCursorPagination
{
    protected function filters(Builder $query, Request $request): Builder
    {
        if (! $request->filter || $request->filter === 'all') {
            $query->withoutTrashed();
        }

        $query->when($request->filter === 'available', fn (Builder $q) => $q->where('is_available', true)->withoutTrashed()
        );

        $query->when($request->filter === 'unavailable', fn (Builder $q) => $q->where('is_available', false)->withoutTrashed()
        );

        $query->when($request->filter === 'deleted', fn (Builder $q) => $q->onlyTrashed()
        );

        $query->when($request->category, fn (Builder $q) => $q->where('category', $request->category)
        );

        $query->when($request->allergen, fn (Builder $q) => $q->where('allergen_id', $request->allergen)
        );

        $query->when($request->search, fn (Builder $q) => $q->whereTranslationLike(
            'name',
            '%'.$request->string('search')->trim().'%',
            app()->getLocale(),
        )
        );

        return $query;
    }

    protected function paginate(Builder $query, int $perPage): CursorPaginator
    {
        return $query
            ->orderBy('updated_at', 'desc')
            ->cursorPaginate($perPage);
    }

    /**
     * @param  class-string<JsonResource>  $resource
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
