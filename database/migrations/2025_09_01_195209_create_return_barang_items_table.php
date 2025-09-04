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
        Schema::create('return_barang_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('return_barang_id');
            $table->string('kode_barang');
            $table->string('nama_barang');
            $table->text('keterangan')->nullable();
            $table->decimal('qty_return', 10, 2);
            $table->string('satuan');
            $table->decimal('harga', 15, 2);
            $table->decimal('total', 15, 2);
            $table->enum('status_item', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('catatan_item')->nullable();
            $table->timestamps();

            // Foreign keys
            $table->foreign('return_barang_id')->references('id')->on('return_barang')->onDelete('cascade');
            $table->foreign('kode_barang')->references('kode_barang')->on('kode_barangs')->onDelete('cascade');

            // Indexes
            $table->index(['return_barang_id']);
            $table->index(['kode_barang']);
            $table->index(['status_item']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('return_barang_items');
    }
};