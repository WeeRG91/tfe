<?php

namespace App\Models;

use App\Enums\WeekdayEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RestaurantHour extends Model
{
    protected $guarded = ['id'];

    public function periods(): HasMany
    {
        return $this->hasMany(RestaurantHourPeriod::class)->orderBy('position');
    }

    protected function casts(): array
    {
        return [
            'weekday' => WeekdayEnum::class,
            'is_open' => 'boolean',
        ];
    }
}
