<?php

namespace Database\Seeders;

use App\Enums\WeekdayEnum;
use App\Models\RestaurantHour;
use Illuminate\Database\Seeder;

class RestaurantHoursSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (WeekdayEnum::cases() as $weekday) {
            $day = RestaurantHour::query()->firstOrCreate(
                [
                    'weekday' => $weekday->value,
                ],
                [
                    'is_open' => true,
                ],
            );

            if (! $day->is_open) {
                continue;
            }

            $day->periods()->firstOrCreate(
                ['position' => 1],
                [
                    'opens_at' => '11:00:00',
                    'closes_at' => '22:00:00',
                    'last_pickup_at' => '21:00:00',
                ],
            );
        }
    }
}
