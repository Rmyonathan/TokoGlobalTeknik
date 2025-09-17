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
        Schema::table('transaksi_item_sumber', function (Blueprint $table) {
            $table->foreignId('surat_jalan_item_sumber_id')->nullable()->after('harga_modal')
                  ->constrained('surat_jalan_item_sumber')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transaksi_item_sumber', function (Blueprint $table) {
            $table->dropForeign(['surat_jalan_item_sumber_id']);
            $table->dropColumn('surat_jalan_item_sumber_id');
        });
    }
};