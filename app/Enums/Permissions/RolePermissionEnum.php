<?php

namespace App\Enums\Permissions;

enum RolePermissionEnum: string
{
    case ROLE_VIEW = 'role.view';
    case ROLE_CREATE = 'role.create';
    case ROLE_UPDATE = 'role.update';
    case ROLE_DELETE = 'role.delete';

    public function label(): string
    {
        return match ($this) {
            self::ROLE_VIEW => 'View roles',
            self::ROLE_CREATE => 'Create roles',
            self::ROLE_UPDATE => 'Update roles',
            self::ROLE_DELETE => 'Delete roles',
        };
    }
}
