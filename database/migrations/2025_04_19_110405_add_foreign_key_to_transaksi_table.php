<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeyToTransaksiTable extends Migration
{
    public function up()
    {
        Schema::table('transaksi', function (Blueprint $table) {
            $table->foreign('kode_customer') // Kolom di tabel transaksi
                  ->references('kode_customer') // Kolom di tabel customers
                  ->on('customers')
                  ->onDelete('cascade') // Hapus transaksi jika customer dihapus
                  ->onUpdate('cascade'); // Update foreign key jika customer diubah
        });
    }

    public function down()
    {
        Schema::table('transaksi', function (Blueprint $table) {
            $table->dropForeign(['kode_customer']);
        });
    }
}