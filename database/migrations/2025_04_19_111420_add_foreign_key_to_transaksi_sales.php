<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeyToTransaksiSales extends Migration
{
    public function up()
    {
        Schema::table('transaksi', function (Blueprint $table) {
            $table->foreign('sales') // Kolom di tabel transaksi
                  ->references('kode_stok_owner') // Kolom di tabel stok_owners
                  ->on('stok_owners')
                  ->onDelete('cascade') // Hapus transaksi jika stok owner dihapus
                  ->onUpdate('cascade'); // Update foreign key jika stok owner diubah
        });
    }

    public function down()
    {
        Schema::table('transaksi', function (Blueprint $table) {
            $table->dropForeign(['sales']);
        });
    }
}