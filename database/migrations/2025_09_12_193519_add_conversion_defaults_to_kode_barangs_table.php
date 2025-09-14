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
            $table->integer('nilai_konversi')->nullable()->after('unit_dasar');
            $table->string('satuan_dasar', 50)->nullable()->after('nilai_konversi');
            $table->string('satuan_besar', 50)->nullable()->after('satuan_dasar');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kode_barangs', function (Blueprint $table) {
            $table->dropColumn(['nilai_konversi', 'satuan_dasar', 'satuan_besar']);
        });
    }
};
