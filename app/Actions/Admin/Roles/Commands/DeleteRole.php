<?php

namespace App\Actions\Admin\Roles\Commands;

use Exception;
use Spatie\Permission\Models\Role;

class DeleteRole
{
    /**
     * @throws Exception
     */
    public function execute(Role $role): void
    {
        if ($role->name == 'Admin') {
            throw new Exception(
                "You can't delete admin role"
            );
        }

        $role->permissions()->detach();

        $role->delete();
    }
}
