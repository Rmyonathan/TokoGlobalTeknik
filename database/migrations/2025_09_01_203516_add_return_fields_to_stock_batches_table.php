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
        Schema::table('stock_batches', function (Blueprint $table) {
            $table->string('customer_id')->nullable()->after('keterangan')->comment('Customer ID untuk return dari penjualan');
            $table->string('tipe_batch')->default('pembelian')->after('customer_id')->comment('Tipe batch: pembelian, return_penjualan, return_supplier');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_batches', function (Blueprint $table) {
            $table->dropColumn(['customer_id', 'tipe_batch']);
        });
    }
};