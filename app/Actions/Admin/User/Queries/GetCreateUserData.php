<?php

namespace App\Actions\Admin\User\Queries;

use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class GetCreateUserData
{
    /**
     * @return array
     */
    public function execute(): array
    {
        $roles = Role::with('permissions')
            ->whereNot('name', 'Super Admin')
            ->orderBy('name')
            ->get();

        $permissions = Permission::query()
            ->orderBy('category')
            ->orderBy('name')
            ->get();

        return [
            'roles' => $roles,
            'permissions' => $permissions,
        ];
    }
}
