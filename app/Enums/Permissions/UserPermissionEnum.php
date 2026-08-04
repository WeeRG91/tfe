<?php

namespace App\Enums\Permissions;

use App\Enums\Permissions\Traits\HasTranslatablePermissionLabel;

enum UserPermissionEnum: string
{
    use HasTranslatablePermissionLabel;

    case USER_VIEW = 'user.view';
    case USER_CREATE = 'user.create';
    case USER_UPDATE = 'user.update';
    case USER_DELETE = 'user.delete';
    case USER_RESTORE = 'user.restore';

}
