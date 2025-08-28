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
        Schema::create('pembayaran_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pembayaran_id')->constrained('pembayarans')->onDelete('cascade');
            $table->foreignId('transaksi_id')->constrained('transaksi')->onDelete('cascade');
            $table->string('no_transaksi', 50); // No faktur
            $table->decimal('total_faktur', 15, 2); // Total faktur asli
            $table->decimal('sudah_dibayar', 15, 2); // Total yang sudah dibayar sebelumnya
            $table->decimal('jumlah_dilunasi', 15, 2); // Jumlah yang dilunasi dalam pembayaran ini
            $table->decimal('sisa_tagihan', 15, 2); // Sisa tagihan setelah pembayaran ini
            $table->enum('status_pelunasan', ['lunas', 'sebagian', 'belum_dibayar'])->default('belum_dibayar');
            $table->text('keterangan')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index(['pembayaran_id']);
            $table->index(['transaksi_id']);
            $table->index(['no_transaksi']);
            $table->index(['status_pelunasan']);
            
            // Unique constraint untuk mencegah double payment
            $table->unique(['pembayaran_id', 'transaksi_id'], 'pembayaran_transaksi_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pembayaran_details');
    }
};
