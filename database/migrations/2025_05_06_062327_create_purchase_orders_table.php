<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePurchaseOrdersTable extends Migration
{
    public function up()
    {
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->string('no_po')->unique();
            $table->date('tanggal');
            $table->string('kode_customer');
            $table->string('sales')->nullable();
            $table->string('pembayaran')->nullable();
            $table->string('cara_bayar')->nullable();
            $table->date('tanggal_jadi')->nullable(); // NULL dulu bro
            $table->double('subtotal')->default(0);
            $table->double('discount')->default(0);
            $table->double('disc_rupiah')->default(0);
            $table->double('ppn')->default(0);
            $table->double('dp')->default(0);
            $table->double('grand_total')->default(0);
            $table->enum('status', ['pending', 'completed'])->default('pending');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('purchase_orders');
    }
}
