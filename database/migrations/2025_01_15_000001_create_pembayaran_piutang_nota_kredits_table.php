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
        if (!Schema::hasTable('pembayaran_piutang_nota_kredits')) {
            Schema::create('pembayaran_piutang_nota_kredits', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('pembayaran_id');
                $table->unsignedBigInteger('nota_kredit_id');
                $table->string('no_nota_kredit', 50);
                $table->decimal('total_nota_kredit', 15, 2); // Total nota kredit
                $table->decimal('jumlah_digunakan', 15, 2); // Jumlah yang digunakan sebagai pemotong
                $table->decimal('sisa_nota_kredit', 15, 2); // Sisa nota kredit setelah digunakan
                $table->text('keterangan')->nullable();
                $table->timestamps();
                
                // Indexes
                $table->index(['pembayaran_id'], 'idx_pp_nk_pembayaran_id');
                $table->index(['nota_kredit_id'], 'idx_pp_nk_nota_kredit_id');
                $table->index(['no_nota_kredit'], 'idx_pp_nk_no_nota_kredit');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pembayaran_piutang_nota_kredits');
    }
};
