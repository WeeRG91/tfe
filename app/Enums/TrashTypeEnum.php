<?php

namespace App\Enums;

use App\Models\Allergen;
use App\Models\Dish;
use App\Models\Drink;
use App\Models\Ingredient;
use App\Models\Meat;

enum TrashTypeEnum: string
{
    case Dish = 'Dish';
    case Ingredient = 'Ingredient';
    case Allergen = 'Allergen';
    case Drink = 'Drink';
    case Meat = 'Meat';

    public function model(): string
    {
        return match ($this) {
            self::Dish => Dish::class,
            self::Ingredient => Ingredient::class,
            self::Allergen => Allergen::class,
            self::Drink => Drink::class,
            self::Meat => Meat::class,
        };
    }
}
