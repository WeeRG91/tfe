<?php

namespace App\Enums;

enum DishCategoryEnum: int
{
    case APPETIZER = 1;
    case MAIN_COURSE = 2;
    case SOUP = 3;
    case NOODLES = 4;
    case DESSERT = 5;
    case VEGETARIAN = 6;

    public function label(): string
    {
        return match ($this) {
            DishCategoryEnum::APPETIZER => 'Appetizer',
            DishCategoryEnum::MAIN_COURSE => 'Main course',
            DishCategoryEnum::SOUP => 'Soup',
            DishCategoryEnum::NOODLES => 'Noodles',
            DishCategoryEnum::DESSERT => 'Dessert',
            DishCategoryEnum::VEGETARIAN => 'Vegetarian',
        };
    }

    public static function getCategories(): array
    {
        return array_map(fn($case) => [
            'value' => $case->value,
            'label' => $case->label(),
        ], self::cases());
    }
}

