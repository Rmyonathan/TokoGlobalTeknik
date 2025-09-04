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
        Schema::create('nota_debit', function (Blueprint $table) {
            $table->id();
            $table->string('no_nota_debit')->unique();
            $table->date('tanggal');
            $table->string('kode_supplier');
            $table->unsignedBigInteger('retur_pembelian_id')->nullable();
            $table->decimal('total_debit', 15, 2);
            $table->text('keterangan')->nullable();
            $table->enum('status', ['pending', 'approved', 'processed'])->default('pending');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->foreign('kode_supplier')->references('kode_supplier')->on('suppliers')->onDelete('cascade');
            $table->foreign('retur_pembelian_id')->references('id')->on('retur_pembelian')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nota_debit');
    }
};
