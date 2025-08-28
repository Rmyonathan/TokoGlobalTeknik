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
        Schema::create('pembayarans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->string('no_pembayaran', 50)->unique(); // Nomor pembayaran otomatis
            $table->date('tanggal_bayar');
            $table->decimal('total_bayar', 15, 2); // Total yang dibayarkan customer
            $table->decimal('total_piutang', 15, 2); // Total piutang sebelum pembayaran
            $table->decimal('sisa_piutang', 15, 2); // Sisa piutang setelah pembayaran
            $table->string('metode_pembayaran', 50); // Tunai, Transfer, Cek, dll
            $table->string('cara_bayar', 50); // Detail cara bayar
            $table->string('no_referensi', 100)->nullable(); // No cek, no transfer, dll
            $table->text('keterangan')->nullable();
            $table->enum('status', ['pending', 'confirmed', 'cancelled'])->default('pending');
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('confirmed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index(['customer_id', 'tanggal_bayar']);
            $table->index(['no_pembayaran']);
            $table->index(['status']);
            $table->index(['created_by']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pembayarans');
    }
};
