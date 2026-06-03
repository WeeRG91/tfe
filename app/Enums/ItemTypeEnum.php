<?php

namespace App\Enums;

use App\Models\Dish;
use App\Models\Drink;

enum ItemTypeEnum: int
{
    case DISH = 1;
    case DRINK = 2;

    public function model(): string
    {
        return match ($this) {
            self::DISH => Dish::class,
            self::DRINK => Drink::class,
        };
    }

    public static function fromModel(string $modelClass): self
    {
        return match ($modelClass) {
            Dish::class => self::DISH,
            Drink::class => self::DRINK,
            default => null,
        };
    }
}
