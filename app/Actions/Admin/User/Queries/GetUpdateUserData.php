<?php

namespace App\Actions\Admin\User\Queries;

use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class GetUpdateUserData
{
    /**
     * @param User $user
     * @return array
     */
    public function execute(User $user): array
    {
        $user->load(['roles', 'roles.permissions', 'permissions',]);

        $roles = Role::with('permissions')
            ->whereNot('name', 'Super Admin')
            ->orderBy('name')
            ->get();

        $permissions = Permission::query()
            ->orderBy('category')
            ->orderBy('name')
            ->get();

        return [
            'user' => $user,
            'roles' => $roles,
            'permissions' => $permissions,
        ];
    }
}
