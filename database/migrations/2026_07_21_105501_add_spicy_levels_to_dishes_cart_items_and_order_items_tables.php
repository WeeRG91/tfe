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
        Schema::table('dishes', function (Blueprint $table) {
            $table->unsignedTinyInteger('default_spicy_level')
                ->default(0)
                ->after('category');
        });

        Schema::table('cart_items', function (Blueprint $table) {
            $table->unsignedTinyInteger('spicy_level')
                ->nullable()
                ->after('quantity');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->unsignedTinyInteger('spicy_level')
                ->nullable()
                ->after('quantity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dishes', function (Blueprint $table) {
            $table->dropColumn('default_spicy_level');
        });

        Schema::table('cart_items', function (Blueprint $table) {
            $table->dropColumn('spicy_level');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn('spicy_level');
        });
    }
};
