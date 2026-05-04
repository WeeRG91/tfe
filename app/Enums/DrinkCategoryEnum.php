<?php

namespace App\Enums;

use Illuminate\Support\Arr;

enum DrinkCategoryEnum: int
{
    case SOFT_DRINK = 1;
    case HOT_DRINK = 2;
    case SMOOTHIE = 3;
    case BEER = 4;
    case WINE = 5;
    case COCKTAIL = 6;
    case MOCKTAIL = 7;

    public function label(): string
    {
        return match ($this) {
            self::SOFT_DRINK => 'Soft drink',
            self::HOT_DRINK => 'Hot drink',
            self::SMOOTHIE => 'Smoothie',
            self::BEER => 'Beer',
            self::WINE => 'Wine',
            self::COCKTAIL => 'Cocktail',
            self::MOCKTAIL => 'Mocktail',
        };
    }

    public static function getColor(self $case): string
    {
        return match ($case) {
            self::SOFT_DRINK => 'bg-[#3B82F6]/75',
            self::HOT_DRINK => 'bg-[#F59E0B]/75',
            self::SMOOTHIE => 'bg-[#A855F7]/75',
            self::BEER => 'bg-[#FACC15]/75',
            self::WINE => 'bg-[#7E22CE]/75',
            self::COCKTAIL => 'bg-[#EC4899]/75',
            self::MOCKTAIL => 'bg-[#10B981]/75',
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

    public static function getCategory(self $case): array
    {
        return Arr::first(
            self::getCategories(),
            fn($item) => $item['value'] === $case->value
        );
    }
}
