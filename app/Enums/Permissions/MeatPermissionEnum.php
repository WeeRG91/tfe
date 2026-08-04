<?php

namespace App\Enums\Permissions;

use App\Enums\Permissions\Traits\HasTranslatablePermissionLabel;

enum MeatPermissionEnum: string
{
    use HasTranslatablePermissionLabel;

    case MEAT_VIEW = 'meat.view';
    case MEAT_CREATE = 'meat.create';
    case MEAT_UPDATE = 'meat.update';
    case MEAT_DELETE = 'meat.delete';
    case MEAT_RESTORE = 'meat.restore';

}
