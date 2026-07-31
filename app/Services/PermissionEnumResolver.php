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
    public static function label(string $permission): string
    {
        return match ($permission) {
            AdminPermissionEnum::ADMIN_ACCESS->value => AdminPermissionEnum::ADMIN_ACCESS->label(),
            AdminPermissionEnum::DASHBOARD_VIEW->value => AdminPermissionEnum::DASHBOARD_VIEW->label(),

            AllergenPermissionEnum::ALLERGEN_VIEW->value => AllergenPermissionEnum::ALLERGEN_VIEW->label(),
            AllergenPermissionEnum::ALLERGEN_CREATE->value => AllergenPermissionEnum::ALLERGEN_CREATE->label(),
            AllergenPermissionEnum::ALLERGEN_UPDATE->value => AllergenPermissionEnum::ALLERGEN_UPDATE->label(),
            AllergenPermissionEnum::ALLERGEN_DELETE->value => AllergenPermissionEnum::ALLERGEN_DELETE->label(),
            AllergenPermissionEnum::ALLERGEN_RESTORE->value => AllergenPermissionEnum::ALLERGEN_RESTORE->label(),

            ChatPermissionEnum::CHAT_VIEW->value => ChatPermissionEnum::CHAT_VIEW->label(),
            ChatPermissionEnum::CHAT_DELETE->value => ChatPermissionEnum::CHAT_DELETE->label(),

            DishPermissionEnum::DISH_VIEW->value => DishPermissionEnum::DISH_VIEW->label(),
            DishPermissionEnum::DISH_CREATE->value => DishPermissionEnum::DISH_CREATE->label(),
            DishPermissionEnum::DISH_UPDATE->value => DishPermissionEnum::DISH_UPDATE->label(),
            DishPermissionEnum::DISH_DELETE->value => DishPermissionEnum::DISH_DELETE->label(),
            DishPermissionEnum::DISH_RESTORE->value => DishPermissionEnum::DISH_RESTORE->label(),

            DrinkPermissionEnum::DRINK_VIEW->value => DrinkPermissionEnum::DRINK_VIEW->label(),
            DrinkPermissionEnum::DRINK_CREATE->value => DrinkPermissionEnum::DRINK_CREATE->label(),
            DrinkPermissionEnum::DRINK_UPDATE->value => DrinkPermissionEnum::DRINK_UPDATE->label(),
            DrinkPermissionEnum::DRINK_DELETE->value => DrinkPermissionEnum::DRINK_DELETE->label(),
            DrinkPermissionEnum::DRINK_RESTORE->value => DrinkPermissionEnum::DRINK_RESTORE->label(),

            ImagePermissionEnum::IMAGE_UPDATE->value => ImagePermissionEnum::IMAGE_UPDATE->label(),
            ImagePermissionEnum::IMAGE_DELETE->value => ImagePermissionEnum::IMAGE_DELETE->label(),

            IngredientPermissionEnum::INGREDIENT_VIEW->value => IngredientPermissionEnum::INGREDIENT_VIEW->label(),
            IngredientPermissionEnum::INGREDIENT_CREATE->value => IngredientPermissionEnum::INGREDIENT_CREATE->label(),
            IngredientPermissionEnum::INGREDIENT_UPDATE->value => IngredientPermissionEnum::INGREDIENT_UPDATE->label(),
            IngredientPermissionEnum::INGREDIENT_DELETE->value => IngredientPermissionEnum::INGREDIENT_DELETE->label(),
            IngredientPermissionEnum::INGREDIENT_RESTORE->value => IngredientPermissionEnum::INGREDIENT_RESTORE->label(),

            MeatPermissionEnum::MEAT_VIEW->value => MeatPermissionEnum::MEAT_VIEW->label(),
            MeatPermissionEnum::MEAT_CREATE->value => MeatPermissionEnum::MEAT_CREATE->label(),
            MeatPermissionEnum::MEAT_UPDATE->value => MeatPermissionEnum::MEAT_UPDATE->label(),
            MeatPermissionEnum::MEAT_DELETE->value => MeatPermissionEnum::MEAT_DELETE->label(),
            MeatPermissionEnum::MEAT_RESTORE->value => MeatPermissionEnum::MEAT_RESTORE->label(),

            MessagePermissionEnum::MESSAGE_VIEW->value => MessagePermissionEnum::MESSAGE_VIEW->label(),
            MessagePermissionEnum::MESSAGE_SEND->value => MessagePermissionEnum::MESSAGE_SEND->label(),
            MessagePermissionEnum::MESSAGE_UNSEND->value => MessagePermissionEnum::MESSAGE_UNSEND->label(),
            MessagePermissionEnum::MESSAGE_UPDATE->value => MessagePermissionEnum::MESSAGE_UPDATE->label(),
            MessagePermissionEnum::MESSAGE_DELETE->value => MessagePermissionEnum::MESSAGE_DELETE->label(),

            OrderPermissionEnum::ORDER_VIEW->value => OrderPermissionEnum::ORDER_VIEW->label(),
            OrderPermissionEnum::ORDER_UPDATE->value => OrderPermissionEnum::ORDER_UPDATE->label(),
            OrderPermissionEnum::ORDER_CANCEL->value => OrderPermissionEnum::ORDER_CANCEL->label(),

            RolePermissionEnum::ROLE_VIEW->value => RolePermissionEnum::ROLE_VIEW->label(),
            RolePermissionEnum::ROLE_CREATE->value => RolePermissionEnum::ROLE_CREATE->label(),
            RolePermissionEnum::ROLE_UPDATE->value => RolePermissionEnum::ROLE_UPDATE->label(),
            RolePermissionEnum::ROLE_DELETE->value => RolePermissionEnum::ROLE_DELETE->label(),

            UserPermissionEnum::USER_VIEW->value => UserPermissionEnum::USER_VIEW->label(),
            UserPermissionEnum::USER_CREATE->value => UserPermissionEnum::USER_CREATE->label(),
            UserPermissionEnum::USER_UPDATE->value => UserPermissionEnum::USER_UPDATE->label(),
            UserPermissionEnum::USER_DELETE->value => UserPermissionEnum::USER_DELETE->label(),
            UserPermissionEnum::USER_RESTORE->value => UserPermissionEnum::USER_RESTORE->label(),
        };
    }
}
