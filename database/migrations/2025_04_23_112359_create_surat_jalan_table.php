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
            $table->string('kode_customer');
            $table->string('alamat_suratjalan')->nullable();
            $table->string('no_transaksi')->nullable();
            $table->date('tanggal_transaksi')->nullable();
            $table->decimal('titipan_uang', 15, 2)->default(0);
            $table->decimal('sisa_piutang', 15, 2)->default(0);
            $table->timestamps();

            $table->foreign('kode_customer')->references('kode_customer')->on('customers')->onDelete('restrict');
            $table->foreign('no_transaksi')->references('no_transaksi')->on('transaksi')->onDelete('restrict');
        });

        Schema::create('surat_jalan_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('no_suratjalan');
            $table->unsignedBigInteger('transaksi_id');
            $table->timestamps();

            $table->foreign('no_suratjalan')->references('no_suratjalan')->on('surat_jalan')->onDelete('restrict');
            $table->foreign('transaksi_id')->references('id')->on('transaksi_items')->onDelete('restrict');
        });
    }

    public function down()
    {
        Schema::dropIfExists('surat_jalan_items');
        Schema::dropIfExists('surat_jalan');
    }
}