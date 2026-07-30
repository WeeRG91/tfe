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

    public function key(): string
    {
        return match ($this) {
            self::APPETIZER => 'appetizer',
            self::MAIN_COURSE => 'main_course',
            self::SOUP => 'soup',
            self::NOODLES => 'noodles',
            self::DESSERT => 'dessert',
            self::VEGETARIAN => 'vegetarian',
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::APPETIZER => 'Appetizer',
            self::MAIN_COURSE => 'Main course',
            self::SOUP => 'Soup',
            self::NOODLES => 'Noodles',
            self::DESSERT => 'Dessert',
            self::VEGETARIAN => 'Vegetarian',
        };
    }

    public static function getColor(self $case): string
    {
        return match ($case) {
            self::APPETIZER => 'bg-[#8B5CF6]/75',
            self::MAIN_COURSE => 'bg-[#DC2626]/75',
            self::SOUP => 'bg-[#EA580C]/75',
            self::NOODLES => 'bg-[#D97706]/75',
            self::DESSERT => 'bg-[#DB2777]/75',
            self::VEGETARIAN => 'bg-[#059669]/75',
        };
    }

    public static function getCategories(): array
    {
        return array_map(fn ($case) => [
            'value' => $case->value,
            'key' => $case->key(),
            'label' => $case->label(),
            'color' => self::getColor($case),
        ], self::cases());
    }

    public static function getCategory(self $case): array
    {
        return Arr::first(
            self::getCategories(),
            fn($item) => $item['value'] === $case->value
        );
    }
}

