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
        Schema::create('pembayaran_utang_supplier_nota_debits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pembayaran_utang_supplier_id')->constrained('pembayaran_utang_suppliers', 'id', 'fk_pus_nd_pus_id')->onDelete('cascade');
            $table->foreignId('nota_debit_id')->constrained('nota_debit', 'id', 'fk_pus_nd_nota_debit_id')->onDelete('cascade');
            $table->string('no_nota_debit', 50);
            $table->decimal('total_nota_debit', 15, 2); // Total nota debit
            $table->decimal('jumlah_digunakan', 15, 2); // Jumlah yang digunakan sebagai pemotong
            $table->decimal('sisa_nota_debit', 15, 2); // Sisa nota debit setelah digunakan
            $table->text('keterangan')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index(['pembayaran_utang_supplier_id'], 'idx_pus_nd_pus_id');
            $table->index(['nota_debit_id'], 'idx_pus_nd_nota_debit_id');
            $table->index(['no_nota_debit'], 'idx_pus_nd_no_nota_debit');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pembayaran_utang_supplier_nota_debits');
    }
};
