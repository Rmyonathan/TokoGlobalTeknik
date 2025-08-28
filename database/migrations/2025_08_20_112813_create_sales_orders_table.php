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
        Schema::create('sales_orders', function (Blueprint $table) {
            $table->id();
            $table->string('no_so', 50)->unique(); // Nomor Sales Order
            $table->date('tanggal'); // Tanggal SO
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->foreignId('salesman_id')->constrained('stok_owners')->onDelete('cascade'); // Salesman dari stok_owners
            $table->enum('status', ['pending', 'approved', 'processed', 'canceled'])->default('pending');
            $table->decimal('subtotal', 15, 2)->default(0); // Total sebelum diskon
            $table->decimal('diskon', 15, 2)->default(0); // Diskon
            $table->decimal('grand_total', 15, 2)->default(0); // Total setelah diskon
            $table->text('keterangan')->nullable(); // Keterangan tambahan
            $table->date('tanggal_estimasi')->nullable(); // Tanggal estimasi pengiriman
            $table->string('cara_bayar', 50)->default('Tunai'); // Cara pembayaran
            $table->integer('hari_tempo')->default(0); // Hari tempo jika kredit
            $table->timestamps();
            
            // Index untuk optimasi query
            $table->index(['no_so']);
            $table->index(['tanggal']);
            $table->index(['customer_id']);
            $table->index(['salesman_id']);
            $table->index(['status']);
            $table->index(['tanggal_estimasi']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_orders');
    }
};
