<?php

namespace App\Enums\Permissions;

enum DishPermissionEnum: string
{
    case DISH_VIEW = 'dish.view';
    case DISH_CREATE = 'dish.create';
    case DISH_UPDATE = 'dish.update';
    case DISH_DELETE = 'dish.delete';
    case DISH_RESTORE = 'dish.restore';

    public function label(): string
    {
        return match ($this) {
            self::DISH_VIEW => 'View dishes',
            self::DISH_CREATE => 'Create dishes',
            self::DISH_UPDATE => 'Update dishes',
            self::DISH_DELETE => 'Delete dishes',
            self::DISH_RESTORE => 'Restore dishes',
        };
    }
}
