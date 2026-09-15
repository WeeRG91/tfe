<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $hours = DB::table('restaurant_hours')
            ->where('is_open', true)
            ->whereNotNull('opens_at')
            ->whereNotNull('closes_at')
            ->whereNotNull('last_pickup_at')
            ->get();

        foreach ($hours as $day) {
            DB::table('restaurant_hour_periods')->insert([
                'restaurant_hour_id' => $day->id,
                'position' => 1,
                'opens_at' => $day->opens_at,
                'closes_at' => $day->closes_at,
                'last_pickup_at' => $day->last_pickup_at,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
