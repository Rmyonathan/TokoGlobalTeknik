<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSuratJalanTable extends Migration
{
    public function up()
    {
        Schema::create('surat_jalan', function (Blueprint $table) {
            $table->id();
            $table->string('no_suratjalan')->unique();
            $table->date('tanggal');
            $table->unsignedBigInteger('customer_id');
            $table->string('alamat')->nullable();
            $table->string('alamat_suratjalan')->nullable();
            $table->string('no_transaksi')->nullable();
            $table->date('tanggal_transaksi')->nullable();
            $table->timestamps();

            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
            $table->foreign('no_transaksi')->references('no_transaksi')->on('transaksi')->onDelete('cascade');
        });

        Schema::create('surat_jalan_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('surat_jalan_id');
            $table->unsignedBigInteger('transaksi_item_id');
            $table->integer('qty_dibawa');
            $table->timestamps();

            $table->foreign('surat_jalan_id')->references('id')->on('surat_jalan')->onDelete('cascade');
            $table->foreign('transaksi_item_id')->references('id')->on('transaksi_items')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('surat_jalan_items');
        Schema::dropIfExists('surat_jalan');
    }
}