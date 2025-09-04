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
        Schema::table('transaksi_items', function (Blueprint $table) {
            $table->decimal('qty_return', 10, 2)->default(0)->after('qty')->comment('Quantity yang sudah di-return');
            $table->decimal('qty_sisa', 10, 2)->default(0)->after('qty_return')->comment('Quantity yang tersisa (qty - qty_return)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transaksi_items', function (Blueprint $table) {
            $table->dropColumn(['qty_return', 'qty_sisa']);
        });
    }
};