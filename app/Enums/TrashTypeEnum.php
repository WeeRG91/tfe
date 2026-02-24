<?php

namespace App\Enums;

use App\Models\Allergen;
use App\Models\Dish;
use App\Models\Drink;
use App\Models\Ingredient;

enum TrashTypeEnum: string
{
    case Dish = 'Dish';
    case Ingredient = 'Ingredient';
    case Allergen = 'Allergen';
    case Drink = 'Drink';

    public function model(): string
    {
        return match ($this) {
            self::Dish => Dish::class,
            self::Ingredient => Ingredient::class,
            self::Allergen => Allergen::class,
            self::Drink => Drink::class,
        };
    }
}
