<?php

namespace App\Actions\Admin\Roles\Queries;

use Illuminate\Contracts\Pagination\CursorPaginator;
use Spatie\Permission\Models\Role;

class GetRoles
{
    public function execute(?string $search = null): CursorPaginator
    {
        return Role::query()
            ->with('permissions')
            ->whereNot('name', 'Super Admin')
            ->when($search, function ($query) use ($search) {
                $query->where(
                    'name',
                    'LIKE',
                    "%{$search}%"
                );
            })
            ->orderBy('name')
            ->cursorPaginate(8);
    }
}
