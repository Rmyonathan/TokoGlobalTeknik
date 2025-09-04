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
        Schema::create('pembayaran_utang_supplier_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pembayaran_utang_supplier_id')->constrained('pembayaran_utang_suppliers', 'id', 'fk_pus_details_pus_id')->onDelete('cascade');
            $table->foreignId('pembelian_id')->constrained('pembelian', 'id', 'fk_pus_details_pembelian_id')->onDelete('cascade');
            $table->string('no_pembelian', 50);
            $table->decimal('total_faktur', 15, 2); // Total faktur pembelian
            $table->decimal('sudah_dibayar', 15, 2)->default(0); // Sudah dibayar sebelumnya
            $table->decimal('jumlah_dilunasi', 15, 2); // Jumlah yang dilunasi dalam pembayaran ini
            $table->decimal('sisa_tagihan', 15, 2); // Sisa tagihan setelah pembayaran
            $table->enum('status_pelunasan', ['lunas', 'sebagian'])->default('sebagian');
            $table->text('keterangan')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index(['pembayaran_utang_supplier_id'], 'idx_pus_details_pus_id');
            $table->index(['pembelian_id'], 'idx_pus_details_pembelian_id');
            $table->index(['no_pembelian'], 'idx_pus_details_no_pembelian');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pembayaran_utang_supplier_details');
    }
};
