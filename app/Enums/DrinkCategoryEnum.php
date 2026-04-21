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
            DrinkCategoryEnum::SOFT_DRINK => 'Soft drink',
            DrinkCategoryEnum::HOT_DRINK => 'Hot drink',
            DrinkCategoryEnum::SMOOTHIE => 'Smoothie',
            DrinkCategoryEnum::BEER => 'Beer',
            DrinkCategoryEnum::WINE => 'Wine',
            DrinkCategoryEnum::COCKTAIL => 'Cocktail',
            DrinkCategoryEnum::MOCKTAIL => 'Mocktail',
        };
    }

    public static function getColor(DrinkCategoryEnum $case): string
    {
        return match ($case) {
            DrinkCategoryEnum::SOFT_DRINK => 'bg-[#3B82F6]/75',
            DrinkCategoryEnum::HOT_DRINK => 'bg-[#F59E0B]/75',
            DrinkCategoryEnum::SMOOTHIE => 'bg-[#A855F7]/75',
            DrinkCategoryEnum::BEER => 'bg-[#FACC15]/75',
            DrinkCategoryEnum::WINE => 'bg-[#7E22CE]/75',
            DrinkCategoryEnum::COCKTAIL => 'bg-[#EC4899]/75',
            DrinkCategoryEnum::MOCKTAIL => 'bg-[#10B981]/75',
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

    public static function getCategory(DrinkCategoryEnum $case): array
    {
        return Arr::first(self::getCategories(), fn($item) => $item['value'] === $case->value);
    }
}
