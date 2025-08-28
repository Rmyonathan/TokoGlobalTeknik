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
        Schema::create('customer_item_ongkos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->foreignId('kode_barang_id')->constrained('kode_barangs')->onDelete('cascade');
            $table->decimal('ongkos_kuli_khusus', 15, 2); // Ongkos kuli khusus untuk customer dan item ini
            $table->text('keterangan')->nullable(); // Keterangan tambahan
            $table->boolean('is_active')->default(true); // Status aktif
            $table->timestamps();
            
            // Unique constraint untuk kombinasi customer dan kode barang
            $table->unique(['customer_id', 'kode_barang_id'], 'customer_item_ongkos_unique');
            
            // Index untuk optimasi query
            $table->index(['customer_id']);
            $table->index(['kode_barang_id']);
            $table->index(['is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_item_ongkos');
    }
};
