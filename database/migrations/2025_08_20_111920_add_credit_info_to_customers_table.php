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
        Schema::table('customers', function (Blueprint $table) {
            // Tambahkan kolom untuk sistem kredit dan wilayah
            $table->decimal('limit_kredit', 15, 2)->default(0); // Limit kredit dalam rupiah
            $table->integer('limit_hari_tempo')->default(0); // Jumlah hari tempo (0 = tunai)
            $table->foreignId('wilayah_id')->nullable()->constrained('wilayahs')->onDelete('set null');
            
            // Index untuk optimasi query
            $table->index(['limit_kredit']);
            $table->index(['limit_hari_tempo']);
            $table->index(['wilayah_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropForeign(['wilayah_id']);
            $table->dropIndex(['limit_kredit']);
            $table->dropIndex(['limit_hari_tempo']);
            $table->dropIndex(['wilayah_id']);
            $table->dropColumn([
                'limit_kredit',
                'limit_hari_tempo',
                'wilayah_id'
            ]);
        });
    }
};
