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
        Schema::table('surat_jalan', function (Blueprint $table) {
            $table->string('metode_pembayaran', 50)->nullable()->after('sisa_piutang');
            $table->string('cara_bayar', 50)->nullable()->after('metode_pembayaran');
            $table->integer('hari_tempo')->nullable()->after('cara_bayar');
            $table->date('tanggal_jatuh_tempo')->nullable()->after('hari_tempo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('surat_jalan', function (Blueprint $table) {
            $table->dropColumn(['metode_pembayaran', 'cara_bayar', 'hari_tempo', 'tanggal_jatuh_tempo']);
        });
    }
};
