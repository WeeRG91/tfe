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
        Schema::dropIfExists('cart_item_meats');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('cart_item_meats', function (Blueprint $table) {
            $table->foreignId('cart_item_id')->constrained('cart_items')->cascadeOnDelete();
            $table->foreignId('meat_id')->constrained('meats')->cascadeOnDelete();

            $table->primary(['cart_item_id', 'meat_id']);
        });
    }
};
