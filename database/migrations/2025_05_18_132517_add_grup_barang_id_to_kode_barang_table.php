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
            $table->unsignedBigInteger('grup_barang_id')->nullable()->after('id');
            $table->foreign('grup_barang_id')->references('id')->on('grup_barang')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kode_barangs', function (Blueprint $table) {
            $table->dropForeign(['grup_barang_id']);
            $table->dropColumn('grup_barang_id');
        });
    }
};