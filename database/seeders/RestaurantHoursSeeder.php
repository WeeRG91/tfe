<?php

namespace Database\Seeders;

use App\Enums\WeekdayEnum;
use App\Models\RestaurantHour;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RestaurantHoursSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (WeekdayEnum::cases() as $weekday) {
            RestaurantHour::query()->updateOrCreate(
                [
                    'weekday' => $weekday->value,
                ],
                [
                    'is_open' => true,
                    'opens_at' => '11:00:00',
                    'closes_at' => '22:00:00',
                    'last_pickup_at' => '21:00:00',
                ],
            );
        }
    }
}
