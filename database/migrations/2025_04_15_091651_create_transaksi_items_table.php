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
        Schema::create('transaksi_items', function (Blueprint $table) {
            $table->id();
            $table->string('no_transaksi'); // Menggunakan string untuk relasi ke no_transaksi
            $table->foreign('no_transaksi')->references('no_transaksi')->on('transaksi')->onDelete('cascade');
            $table->unsignedBigInteger('kode_barang');
            $table->foreign('kode_barang')->references('id')->on('panels')->onDelete('cascade');
            $table->string('nama_barang');
            $table->text('keterangan')->nullable();
            $table->decimal('harga', 15, 2);
            $table->decimal('panjang', 8, 2)->default(0);
            $table->decimal('lebar', 8, 2)->default(0);
            $table->integer('qty');
            $table->decimal('diskon', 5, 2)->default(0);
            $table->decimal('total', 15, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaksi_items');
    }
};