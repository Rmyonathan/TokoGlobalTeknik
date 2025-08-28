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
        Schema::create('wilayahs', function (Blueprint $table) {
            $table->id();
            $table->string('nama_wilayah', 100); // Nama wilayah (Jakarta, Bandung, Surabaya, dll)
            $table->text('keterangan')->nullable(); // Keterangan tambahan
            $table->boolean('is_active')->default(true); // Status aktif/nonaktif
            $table->timestamps();
            
            // Index untuk optimasi query
            $table->index(['nama_wilayah']);
            $table->index(['is_active']);
            
            // Unique constraint untuk mencegah duplikasi nama wilayah
            $table->unique(['nama_wilayah'], 'unique_nama_wilayah');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wilayahs');
    }
};
