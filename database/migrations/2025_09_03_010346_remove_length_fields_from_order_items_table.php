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
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn(['length', 'original_panel_length', 'remaining_length']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->decimal('length', 8, 2)->nullable();
            $table->decimal('original_panel_length', 8, 2)->nullable();
            $table->decimal('remaining_length', 8, 2)->nullable();
        });
    }
};
