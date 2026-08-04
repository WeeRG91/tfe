<?php

namespace App\Enums\Permissions;

use App\Enums\Permissions\Traits\HasTranslatablePermissionLabel;

enum DishPermissionEnum: string
{
    use HasTranslatablePermissionLabel;

    case DISH_VIEW = 'dish.view';
    case DISH_CREATE = 'dish.create';
    case DISH_UPDATE = 'dish.update';
    case DISH_DELETE = 'dish.delete';
    case DISH_RESTORE = 'dish.restore';

}
