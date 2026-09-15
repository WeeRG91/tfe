<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('restaurant_hour_periods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_hour_id')
                ->constrained('restaurant_hours')
                ->cascadeOnDelete();
            $table->unsignedTinyInteger('position');
            $table->time('opens_at');
            $table->time('closes_at');
            $table->time('last_pickup_at');
            $table->timestamps();

            $table->unique(['restaurant_hour_id', 'position']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('restaurant_hour_periods');
    }
};
