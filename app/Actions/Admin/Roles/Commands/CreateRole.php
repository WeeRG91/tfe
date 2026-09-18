<?php

namespace App\Actions\Admin\Roles\Commands;

use Spatie\Permission\Models\Role;

class CreateRole
{
    public function execute(string $name, array $permissions = []): Role
    {
        $role = Role::create([
            'name' => $name,
            'guard_name' => 'web',
        ]);

        $role->syncPermissions($permissions);

        return $role;
    }
}
