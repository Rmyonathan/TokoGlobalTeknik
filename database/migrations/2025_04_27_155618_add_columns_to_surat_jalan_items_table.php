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
        Schema::table('surat_jalan_items', function (Blueprint $table) {
            $table->string('kode_barang');
            $table->string('nama_barang');
            $table->integer('qty');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('surat_jalan_items', function (Blueprint $table) {
            $table->dropColumn('kode_barang');
            $table->dropColumn('nama_barang');
            $table->dropColumn('qty');
        });
    }
};
