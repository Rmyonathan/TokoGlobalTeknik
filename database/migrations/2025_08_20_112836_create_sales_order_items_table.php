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
        Schema::create('sales_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sales_order_id')->constrained('sales_orders')->onDelete('cascade');
            $table->foreignId('kode_barang_id')->constrained('kode_barangs')->onDelete('cascade');
            $table->decimal('qty', 15, 2); // Quantity yang dipesan
            $table->string('satuan', 20)->default('LBR'); // Satuan (LBR, DUS, BOX, dll)
            $table->decimal('harga', 15, 2); // Harga per unit
            $table->decimal('total', 15, 2); // Total harga (qty * harga)
            $table->decimal('qty_terkirim', 15, 2)->default(0); // Quantity yang sudah dikirim
            $table->decimal('qty_sisa', 15, 2); // Quantity yang belum dikirim (qty - qty_terkirim)
            $table->text('keterangan')->nullable(); // Keterangan tambahan
            $table->timestamps();
            
            // Index untuk optimasi query
            $table->index(['sales_order_id']);
            $table->index(['kode_barang_id']);
            $table->index(['satuan']);
            $table->index(['qty_sisa']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_order_items');
    }
};
