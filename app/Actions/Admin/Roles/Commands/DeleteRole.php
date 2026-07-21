<?php

namespace App\Actions\Admin\Roles\Commands;

use Exception;
use Illuminate\Http\JsonResponse;
use Spatie\Permission\Models\Role;

class DeleteRole
{
    /**
     * @param Role $role
     * @return void
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
