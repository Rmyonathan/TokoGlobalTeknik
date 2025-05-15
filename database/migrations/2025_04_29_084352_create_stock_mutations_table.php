<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('stock_mutations', function (Blueprint $table) {
            $table->id();
            $table->string('kode_barang');
            $table->string('nama_barang');
            $table->string('no_transaksi');
            $table->dateTime('tanggal');
            $table->string('no_nota')->nullable();
            $table->string('supplier_customer');
            $table->decimal('plus', 12, 2)->default(0);
            $table->decimal('minus', 12, 2)->default(0);
            $table->decimal('total', 12, 2);
            $table->string('so')->default('default'); // Changed default to 'default'
            $table->string('satuan')->default('LBR');
            $table->string('keterangan')->nullable();
            $table->timestamps();

            $table->index('kode_barang');
            $table->index('tanggal');
            // Removed the index on ['kode_barang', 'so']
        });
    }

    public function down()
    {
        Schema::dropIfExists('stock_mutations');
    }
};