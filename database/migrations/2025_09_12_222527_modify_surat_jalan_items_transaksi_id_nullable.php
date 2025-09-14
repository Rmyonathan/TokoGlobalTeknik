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
            // Drop foreign key constraint first
            $table->dropForeign(['transaksi_id']);
            
            // Modify column to be nullable
            $table->unsignedBigInteger('transaksi_id')->nullable()->change();
            
            // Re-add foreign key constraint with nullable support
            $table->foreign('transaksi_id')->references('id')->on('transaksi_items')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('surat_jalan_items', function (Blueprint $table) {
            // Drop foreign key constraint
            $table->dropForeign(['transaksi_id']);
            
            // Make column not nullable again
            $table->unsignedBigInteger('transaksi_id')->nullable(false)->change();
            
            // Re-add foreign key constraint
            $table->foreign('transaksi_id')->references('id')->on('transaksi_items')->onDelete('restrict');
        });
    }
};