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
        Schema::table('kode_barangs', function (Blueprint $table) {
            // Tambahkan kolom untuk sistem konversi unit dan harga dinamis
            if (!Schema::hasColumn('kode_barangs', 'grup_barang_id')) {
                $table->foreignId('grup_barang_id')->nullable()->constrained('grup_barang')->onDelete('set null');
            }
            if (!Schema::hasColumn('kode_barangs', 'unit_dasar')) {
                $table->string('unit_dasar', 20)->default('LBR'); // Unit dasar (LBR, KG, M, dll)
            }
            if (!Schema::hasColumn('kode_barangs', 'harga_jual')) {
                $table->decimal('harga_jual', 15, 2)->default(0); // Harga jual default
            }
            if (!Schema::hasColumn('kode_barangs', 'ongkos_kuli_default')) {
                $table->decimal('ongkos_kuli_default', 15, 2)->default(0); // Ongkos kuli default
            }
            
            // Index untuk optimasi query
            if (!Schema::hasIndex('kode_barangs', 'kode_barangs_grup_barang_id_index')) {
                $table->index(['grup_barang_id']);
            }
            if (!Schema::hasIndex('kode_barangs', 'kode_barangs_unit_dasar_index')) {
                $table->index(['unit_dasar']);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kode_barangs', function (Blueprint $table) {
            $table->dropForeign(['grup_barang_id']);
            $table->dropIndex(['grup_barang_id']);
            $table->dropIndex(['unit_dasar']);
            $table->dropColumn([
                'grup_barang_id',
                'unit_dasar', 
                'harga_jual',
                'ongkos_kuli_default'
            ]);
        });
    }
};
