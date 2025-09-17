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
        Schema::table('pembelian_items', function (Blueprint $table) {
            // Ukuran value and unit (e.g., 12.5 and 'inch' or 'meter')
            $table->decimal('ukuran', 12, 4)->nullable()->after('qty');
            $table->string('ukuran_unit', 16)->nullable()->after('ukuran');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pembelian_items', function (Blueprint $table) {
            $table->dropColumn(['ukuran', 'ukuran_unit']);
        });
    }
};


