<?php

namespace App\Enums\Permissions;

enum PermissionCategoryEnum: int
{
    case ADMIN = 1;
    case ALLERGEN = 2;
    case CHAT = 3;
    case DISH = 4;
    case DRINK = 5;
    case IMAGE = 6;
    case INGREDIENT = 7;
    case MEAT = 8;
    case MESSAGE = 9;
    case ORDER = 10;
    case ROLE = 11;
    case USER = 12;

    public function label(): string
    {
        return __('permissions.categories.'.strtolower($this->name));
    }
}
