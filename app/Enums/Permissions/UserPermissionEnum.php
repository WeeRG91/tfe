<?php

namespace App\Enums\Permissions;

enum UserPermissionEnum: string
{
    case USER_VIEW = 'user.view';
    case USER_CREATE = 'user.create';
    case USER_UPDATE = 'user.update';
    case USER_DELETE = 'user.delete';
    case USER_RESTORE = 'user.restore';

    public function label(): string
    {
        return match ($this) {
            self::USER_VIEW => 'View Users',
            self::USER_CREATE => 'Create Users',
            self::USER_UPDATE => 'Update Users',
            self::USER_DELETE => 'Delete Users',
            self::USER_RESTORE => 'Restore Users',
        };
    }
}
