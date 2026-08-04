<?php

namespace App\Services;

use App\Enums\Permissions\AdminPermissionEnum;
use App\Enums\Permissions\AllergenPermissionEnum;
use App\Enums\Permissions\ChatPermissionEnum;
use App\Enums\Permissions\DishPermissionEnum;
use App\Enums\Permissions\DrinkPermissionEnum;
use App\Enums\Permissions\ImagePermissionEnum;
use App\Enums\Permissions\IngredientPermissionEnum;
use App\Enums\Permissions\MeatPermissionEnum;
use App\Enums\Permissions\MessagePermissionEnum;
use App\Enums\Permissions\OrderPermissionEnum;
use App\Enums\Permissions\RolePermissionEnum;
use App\Enums\Permissions\UserPermissionEnum;

class PermissionEnumResolver
{
    /**
     * @var list<class-string>
     */
    private const PERMISSION_ENUMS = [
        AdminPermissionEnum::class,
        AllergenPermissionEnum::class,
        ChatPermissionEnum::class,
        DishPermissionEnum::class,
        DrinkPermissionEnum::class,
        ImagePermissionEnum::class,
        IngredientPermissionEnum::class,
        MeatPermissionEnum::class,
        MessagePermissionEnum::class,
        OrderPermissionEnum::class,
        RolePermissionEnum::class,
        UserPermissionEnum::class,
    ];

    public static function label(string $permission): string
    {
        foreach (self::PERMISSION_ENUMS as $enum) {
            if ($case = $enum::tryFrom($permission)) {
                return $case->label();
            }
        }

        return $permission;
    }
}
