<?php

namespace App\Actions\Admin\Roles\Commands;

use Spatie\Permission\Models\Role;

class UpdateRole
{
    /**
     * @param Role $role
     * @param string $name
     * @param array $permissions
     * @return Role
     */
    public function execute(
        Role $role,
        string $name,
        array $permissions = []
    ): Role
    {
        $role->update([
            'name' => $name,
        ]);

        $role->syncPermissions($permissions);

        return $role;
    }
}
