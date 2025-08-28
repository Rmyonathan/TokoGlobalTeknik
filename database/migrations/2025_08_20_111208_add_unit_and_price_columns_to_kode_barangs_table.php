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
            $table->foreignId('kategori_barang_id')->nullable()->constrained('kategori_barang')->onDelete('set null');
            $table->string('unit_dasar', 20)->default('LBR'); // Unit dasar (LBR, KG, M, dll)
            $table->decimal('harga_jual', 15, 2)->default(0); // Harga jual default
            $table->decimal('ongkos_kuli_default', 15, 2)->default(0); // Ongkos kuli default
            
            // Index untuk optimasi query
            $table->index(['kategori_barang_id']);
            $table->index(['unit_dasar']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kode_barangs', function (Blueprint $table) {
            $table->dropForeign(['kategori_barang_id']);
            $table->dropIndex(['kategori_barang_id']);
            $table->dropIndex(['unit_dasar']);
            $table->dropColumn([
                'kategori_barang_id',
                'unit_dasar', 
                'harga_jual',
                'ongkos_kuli_default'
            ]);
        });
    }
};
