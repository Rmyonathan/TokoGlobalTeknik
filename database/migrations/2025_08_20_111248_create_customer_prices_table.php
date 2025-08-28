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
        Schema::create('customer_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->foreignId('kode_barang_id')->constrained('kode_barangs')->onDelete('cascade');
            $table->decimal('harga_jual_khusus', 15, 2); // Harga jual khusus untuk customer ini
            $table->decimal('ongkos_kuli_khusus', 15, 2)->nullable(); // Ongkos kuli khusus (opsional)
            $table->string('unit_jual', 20)->default('LBR'); // Unit untuk penjualan (LBR, DUS, BOX, dll)
            $table->boolean('is_active')->default(true); // Status aktif/nonaktif
            $table->text('keterangan')->nullable(); // Keterangan tambahan
            $table->timestamps();
            
            // Index untuk optimasi query
            $table->index(['customer_id', 'kode_barang_id']);
            $table->index(['customer_id', 'is_active']);
            $table->index(['kode_barang_id', 'is_active']);
            
            // Unique constraint untuk mencegah duplikasi harga per customer per barang
            $table->unique(['customer_id', 'kode_barang_id'], 'unique_price_per_customer_barang');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_prices');
    }
};
