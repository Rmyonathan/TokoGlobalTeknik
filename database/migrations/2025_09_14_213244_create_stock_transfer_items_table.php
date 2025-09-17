<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('stock_transfer_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('stock_transfer_id');
            $table->string('kode_barang');
            $table->string('nama_barang');
            $table->decimal('qty_transfer', 12, 2);
            $table->string('satuan');
            $table->decimal('harga_per_unit', 12, 2)->default(0);
            $table->decimal('total_value', 12, 2)->default(0);
            $table->text('keterangan')->nullable();
            $table->timestamps();

            $table->foreign('stock_transfer_id')->references('id')->on('stock_transfers')->onDelete('cascade');
            $table->index('kode_barang');
        });
    }

    public function down()
    {
        Schema::dropIfExists('stock_transfer_items');
    }
};