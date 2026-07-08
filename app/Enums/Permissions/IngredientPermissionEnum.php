<?php

namespace App\Enums\Permissions;

enum IngredientPermissionEnum: string
{
    case INGREDIENT_VIEW = 'ingredient.view';
    case INGREDIENT_CREATE = 'ingredient.create';
    case INGREDIENT_UPDATE = 'ingredient.update';
    case INGREDIENT_DELETE = 'ingredient.delete';
    case INGREDIENT_RESTORE = 'ingredient.restore';

    public function label(): string
    {
        return match ($this) {
            self::INGREDIENT_VIEW => 'View ingredients',
            self::INGREDIENT_CREATE => 'Create ingredients',
            self::INGREDIENT_UPDATE => 'Update ingredients',
            self::INGREDIENT_DELETE => 'Delete ingredients',
            self::INGREDIENT_RESTORE => 'Restore ingredients',
        };
    }
}
