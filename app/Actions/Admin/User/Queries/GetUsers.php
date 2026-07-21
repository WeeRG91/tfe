<?php

namespace App\Actions\Admin\User\Queries;

use App\Models\User;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Http\Request;

class GetUsers
{
    /**
     * @param Request $request
     * @return CursorPaginator
     */
    public function execute(Request $request): CursorPaginator
    {
        return User::withTrashed()
            ->with('roles')
            ->where('name', '!=', 'Super Admin')
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%$search%")
                        ->orWhere('email', 'like', "%$search%");
                });
            })
            ->when($request->filter, function ($query, $filter) {
                match ($filter) {
                    'active' => $query->withoutTrashed(),
                    'inactive' => $query->onlyTrashed(),
                    default => null,
                };
            })
            ->orderBy('name')
            ->cursorPaginate(8);
    }
}
