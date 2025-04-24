<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePembelianItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pembelian_items', function (Blueprint $table) {
            $table->id();
            $table->string('nota');
            $table->string('kode_barang');
            $table->string('nama_barang');
            $table->text('keterangan')->nullable();
            $table->decimal('harga', 15, 2);
            $table->integer('qty');
            $table->decimal('panjang', 10, 2)->nullable();
            $table->decimal('diskon', 5, 2)->default(0);
            $table->decimal('total', 15, 2);
            $table->timestamps();
            
            // Foreign key relationship
            $table->foreign('nota')
                  ->references('nota')
                  ->on('pembelian')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pembelian_items');
    }
}