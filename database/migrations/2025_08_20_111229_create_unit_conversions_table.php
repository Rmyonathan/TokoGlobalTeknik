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
        Schema::create('unit_conversions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kode_barang_id')->constrained('kode_barangs')->onDelete('cascade');
            $table->string('unit_turunan', 20); // Unit turunan (DUS, BOX, PACK, dll)
            $table->integer('nilai_konversi'); // Nilai konversi ke unit dasar (misal: 1 DUS = 40 LBR)
            $table->text('keterangan')->nullable(); // Keterangan tambahan
            $table->boolean('is_active')->default(true); // Status aktif/nonaktif
            $table->timestamps();
            
            // Index untuk optimasi query
            $table->index(['kode_barang_id', 'unit_turunan']);
            $table->index(['kode_barang_id', 'is_active']);
            
            // Unique constraint untuk mencegah duplikasi unit turunan per barang
            $table->unique(['kode_barang_id', 'unit_turunan'], 'unique_unit_per_barang');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('unit_conversions');
    }
};
