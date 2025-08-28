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
        Schema::create('stock_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kode_barang_id')->constrained('kode_barangs')->onDelete('cascade');
            $table->foreignId('pembelian_item_id')->nullable()->constrained('pembelian_items')->onDelete('set null');
            $table->decimal('qty_masuk', 15, 2); // Quantity yang masuk
            $table->decimal('qty_sisa', 15, 2); // Quantity yang tersisa (untuk FIFO)
            $table->decimal('harga_beli', 15, 2); // Harga beli per unit
            $table->date('tanggal_masuk'); // Tanggal barang masuk
            $table->string('batch_number')->nullable(); // Nomor batch untuk tracking
            $table->text('keterangan')->nullable(); // Keterangan tambahan
            $table->timestamps();
            
            // Index untuk optimasi query FIFO
            $table->index(['kode_barang_id', 'tanggal_masuk']);
            $table->index(['kode_barang_id', 'qty_sisa']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_batches');
    }
};
