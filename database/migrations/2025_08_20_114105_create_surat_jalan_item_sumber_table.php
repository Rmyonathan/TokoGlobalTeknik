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
        Schema::create('surat_jalan_item_sumber', function (Blueprint $table) {
            $table->id();
            $table->foreignId('surat_jalan_item_id')->constrained('surat_jalan_items')->onDelete('cascade');
            $table->foreignId('stock_batch_id')->constrained('stock_batches')->onDelete('cascade');
            $table->decimal('qty_diambil', 15, 2); // Quantity yang diambil dari batch ini
            $table->decimal('harga_modal', 15, 2); // Harga modal dari batch ini
            $table->timestamps();
            
            // Index untuk optimasi query
            $table->index(['surat_jalan_item_id']);
            $table->index(['stock_batch_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('surat_jalan_item_sumber');
    }
};
