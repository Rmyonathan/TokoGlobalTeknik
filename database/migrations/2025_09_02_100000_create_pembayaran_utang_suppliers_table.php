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
        Schema::create('pembayaran_utang_suppliers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->constrained('suppliers', 'id', 'fk_pus_supplier_id')->onDelete('cascade');
            $table->string('no_pembayaran', 50)->unique(); // Nomor pembayaran otomatis
            $table->date('tanggal_bayar');
            $table->decimal('total_bayar', 15, 2); // Total yang dibayarkan ke supplier
            $table->decimal('total_utang', 15, 2); // Total utang sebelum pembayaran
            $table->decimal('sisa_utang', 15, 2); // Sisa utang setelah pembayaran
            $table->decimal('total_nota_debit', 15, 2)->default(0); // Total nota debit yang digunakan
            $table->string('metode_pembayaran', 50); // Tunai, Transfer, Cek, dll
            $table->string('cara_bayar', 50); // Detail cara bayar
            $table->string('no_referensi', 100)->nullable(); // No cek, no transfer, dll
            $table->text('keterangan')->nullable();
            $table->enum('status', ['pending', 'confirmed', 'cancelled'])->default('pending');
            $table->foreignId('created_by')->nullable()->constrained('users', 'id', 'fk_pus_created_by')->onDelete('set null');
            $table->foreignId('confirmed_by')->nullable()->constrained('users', 'id', 'fk_pus_confirmed_by')->onDelete('set null');
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index(['supplier_id', 'tanggal_bayar'], 'idx_pus_supplier_tanggal');
            $table->index(['no_pembayaran'], 'idx_pus_no_pembayaran');
            $table->index(['status'], 'idx_pus_status');
            $table->index(['created_by'], 'idx_pus_created_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pembayaran_utang_suppliers');
    }
};
