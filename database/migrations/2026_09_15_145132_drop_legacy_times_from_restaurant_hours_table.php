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
        Schema::table('restaurant_hours', function (Blueprint $table) {
            $table->dropColumn([
                'opens_at',
                'closes_at',
                'last_pickup_at',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('restaurant_hours', function (Blueprint $table) {
            $table->time('opens_at')->nullable();
            $table->time('closes_at')->nullable();
            $table->time('last_pickup_at')->nullable();
        });
    }
};
