<?php

namespace App\Enums\Permissions;

use App\Enums\Permissions\Traits\HasTranslatablePermissionLabel;

enum RolePermissionEnum: string
{
    use HasTranslatablePermissionLabel;

    case ROLE_VIEW = 'role.view';
    case ROLE_CREATE = 'role.create';
    case ROLE_UPDATE = 'role.update';
    case ROLE_DELETE = 'role.delete';

}
