<?php

namespace App\Enums\Permissions;

use App\Enums\Permissions\Traits\HasTranslatablePermissionLabel;

enum IngredientPermissionEnum: string
{
    use HasTranslatablePermissionLabel;

    case INGREDIENT_VIEW = 'ingredient.view';
    case INGREDIENT_CREATE = 'ingredient.create';
    case INGREDIENT_UPDATE = 'ingredient.update';
    case INGREDIENT_DELETE = 'ingredient.delete';
    case INGREDIENT_RESTORE = 'ingredient.restore';

}
