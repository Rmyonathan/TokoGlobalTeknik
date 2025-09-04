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
        Schema::create('retur_pembelian_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('retur_pembelian_id');
            $table->unsignedBigInteger('pembelian_item_id');
            $table->string('kode_barang');
            $table->string('nama_barang');
            $table->decimal('qty_retur', 10, 2);
            $table->string('satuan');
            $table->decimal('harga', 15, 2);
            $table->decimal('total', 15, 2);
            $table->text('alasan')->nullable();
            $table->timestamps();

            $table->foreign('retur_pembelian_id')->references('id')->on('retur_pembelian')->onDelete('cascade');
            $table->foreign('pembelian_item_id')->references('id')->on('pembelian_items')->onDelete('cascade');
            $table->foreign('kode_barang')->references('kode_barang')->on('kode_barangs')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('retur_pembelian_items');
    }
};
