<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePembelianTable extends Migration
{
    public function up()
    {
        Schema::create('pembelian', function (Blueprint $table) {
            $table->id();
            $table->string('nota')->unique(); // Added unique constraint
            $table->date('tanggal');
            $table->string('kode_supplier'); // Changed from 'supplier'
            $table->string('cabang'); // Added cabang field
            $table->string('pembayaran'); // Added pembayaran field
            $table->string('cara_bayar');
            $table->decimal('subtotal', 15, 2);
            $table->decimal('diskon', 15, 2)->default(0); // Keep the name 'diskon' if you prefer
            $table->decimal('ppn', 15, 2)->default(0);
            $table->decimal('grand_total', 15, 2);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('pembelian');
    }
}