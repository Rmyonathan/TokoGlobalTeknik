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
        Schema::create('return_barang', function (Blueprint $table) {
            $table->id();
            $table->string('no_return')->unique();
            $table->date('tanggal');
            $table->string('kode_customer');
            $table->string('no_transaksi_asal'); // Nomor transaksi yang di-return
            $table->enum('tipe_return', ['penjualan', 'pembelian']);
            $table->enum('status', ['pending', 'approved', 'rejected', 'processed'])->default('pending');
            $table->text('alasan_return');
            $table->decimal('total_return', 15, 2)->default(0);
            $table->string('created_by')->nullable();
            $table->string('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->text('catatan_approval')->nullable();
            $table->timestamps();

            // Foreign keys
            $table->foreign('kode_customer')->references('kode_customer')->on('customers')->onDelete('cascade');
            $table->foreign('no_transaksi_asal')->references('no_transaksi')->on('transaksi')->onDelete('cascade');

            // Indexes
            $table->index(['tanggal']);
            $table->index(['kode_customer']);
            $table->index(['status']);
            $table->index(['tipe_return']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('return_barang');
    }
};