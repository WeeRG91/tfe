<?php

namespace App\Enums;

use Illuminate\Support\Arr;

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

    public static function getColor(DishCategoryEnum $case): string
    {
        return match ($case) {
            DishCategoryEnum::APPETIZER => 'bg-[#8B5CF6]/75',
            DishCategoryEnum::MAIN_COURSE => 'bg-[#DC2626]/75',
            DishCategoryEnum::SOUP => 'bg-[#EA580C]/75',
            DishCategoryEnum::NOODLES => 'bg-[#D97706]/75',
            DishCategoryEnum::DESSERT => 'bg-[#DB2777]/75',
            DishCategoryEnum::VEGETARIAN => 'bg-[#059669]/75',
        };
    }

    public static function getCategories(): array
    {
        return array_map(fn($case) => [
            'value' => $case->value,
            'label' => $case->label(),
            'color' => self::getColor($case),
        ], self::cases());
    }

    public static function getCategory(DishCategoryEnum $case): array
    {
        return Arr::first(self::getCategories(), fn($item) => $item['value'] === $case->value);
    }
}

