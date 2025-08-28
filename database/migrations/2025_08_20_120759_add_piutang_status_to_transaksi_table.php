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
        Schema::table('transaksi', function (Blueprint $table) {
            // Status piutang
            $table->enum('status_piutang', ['lunas', 'sebagian', 'belum_dibayar'])->default('belum_dibayar')->after('status');
            
            // Tracking pembayaran
            $table->decimal('total_dibayar', 15, 2)->default(0)->after('grand_total'); // Total yang sudah dibayar
            $table->decimal('sisa_piutang', 15, 2)->default(0)->after('total_dibayar'); // Sisa yang belum dibayar
            
            // Tanggal jatuh tempo dan pembayaran
            $table->date('tanggal_jatuh_tempo')->nullable()->after('tanggal_jadi');
            $table->date('tanggal_pelunasan')->nullable()->after('tanggal_jatuh_tempo');
            
            // Indexes
            $table->index(['status_piutang']);
            $table->index(['tanggal_jatuh_tempo']);
            $table->index(['sisa_piutang']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transaksi', function (Blueprint $table) {
            $table->dropIndex(['status_piutang']);
            $table->dropIndex(['tanggal_jatuh_tempo']);
            $table->dropIndex(['sisa_piutang']);
            
            $table->dropColumn([
                'status_piutang',
                'total_dibayar',
                'sisa_piutang',
                'tanggal_jatuh_tempo',
                'tanggal_pelunasan'
            ]);
        });
    }
};
