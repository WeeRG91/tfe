<?php

namespace App\Models;

use App\Enums\WeekdayEnum;
use Illuminate\Database\Eloquent\Model;

class RestaurantHour extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'weekday' => WeekdayEnum::class,
            'is_open' => 'boolean',
        ];
    }
}
