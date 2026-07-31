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
            $table->index('category');
            $table->index('is_available');
            $table->index('updated_at');
            $table->index('deleted_at');
            $table->index(['category', 'is_available']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dishes', function (Blueprint $table) {
            $table->dropIndex('category');
            $table->dropIndex('is_available');
            $table->dropIndex('updated_at');
            $table->dropIndex('deleted_at');
            $table->dropIndex(['category', 'is_available']);
        });
    }
};
