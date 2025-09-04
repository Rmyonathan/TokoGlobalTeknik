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
        Schema::create('nota_kredit', function (Blueprint $table) {
            $table->id();
            $table->string('no_nota_kredit')->unique();
            $table->date('tanggal');
            $table->string('kode_customer');
            $table->unsignedBigInteger('retur_penjualan_id')->nullable();
            $table->decimal('total_kredit', 15, 2);
            $table->decimal('sisa_kredit', 15, 2)->default(0);
            $table->text('keterangan')->nullable();
            $table->enum('status', ['pending', 'approved', 'processed'])->default('pending');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->foreign('kode_customer')->references('kode_customer')->on('customers')->onDelete('cascade');
            $table->foreign('retur_penjualan_id')->references('id')->on('retur_penjualan')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nota_kredit');
    }
};
