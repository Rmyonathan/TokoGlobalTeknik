<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('transaksi_items', function (Blueprint $table) {
            $table->unsignedBigInteger('transaksi_id')->nullable()->after('id'); // Tambahkan kolom transaksi_id
            $table->foreign('transaksi_id')->references('id')->on('transaksi')->onDelete('cascade'); // Tambahkan foreign key
        });
    }

    public function down()
    {
        Schema::table('transaksi_items', function (Blueprint $table) {
            $table->dropForeign(['transaksi_id']);
            $table->dropColumn('transaksi_id');
        });
    }
};
