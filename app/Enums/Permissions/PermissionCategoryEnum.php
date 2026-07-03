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
    case USER = 11;

    public function label(): string
    {
        return match ($this) {
            self::ADMIN => 'Admin',
            self::ALLERGEN => 'Allergen',
            self::CHAT => 'Chat',
            self::DISH => 'Dish',
            self::DRINK => 'Drink',
            self::IMAGE => 'Image',
            self::INGREDIENT => 'Ingredient',
            self::MEAT => 'Meat',
            self::MESSAGE => 'Message',
            self::ORDER => 'Order',
            self::USER => 'User',
        };
    }

    public static function getPermissionCategories(): array
    {
        return array_map(fn ($case) => [
            'value' => $case->value,
            'label' => $case->label(),
        ], self::cases());
    }
}
