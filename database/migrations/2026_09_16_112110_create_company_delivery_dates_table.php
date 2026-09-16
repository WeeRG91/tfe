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
        Schema::create('company_delivery_dates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delivery_company_id')
                ->constrained('delivery_companies')
                ->cascadeOnDelete();
            $table->date('delivery_date');
            $table->boolean('is_available')->default(true);
            $table->timestamps();

            $table->unique(['delivery_company_id', 'delivery_date']);

            $table->index(['delivery_company_id', 'is_available', 'delivery_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_delivery_dates');
    }
};
