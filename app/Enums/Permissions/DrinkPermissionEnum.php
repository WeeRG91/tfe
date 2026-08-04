<?php

namespace App\Enums\Permissions;

use App\Enums\Permissions\Traits\HasTranslatablePermissionLabel;

enum DrinkPermissionEnum: string
{
    use HasTranslatablePermissionLabel;

    case DRINK_VIEW = 'drink.view';
    case DRINK_CREATE = 'drink.create';
    case DRINK_UPDATE = 'drink.update';
    case DRINK_DELETE = 'drink.delete';
    case DRINK_RESTORE = 'drink.restore';

}
