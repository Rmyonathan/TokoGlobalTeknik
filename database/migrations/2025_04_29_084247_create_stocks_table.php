<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('stocks', function (Blueprint $table) {
            $table->id();
            $table->string('kode_barang');
            $table->string('nama_barang');
            $table->decimal('good_stock', 12, 2)->default(0);
            $table->decimal('bad_stock', 12, 2)->default(0);
            $table->string('so')->default('ALUMKA');
            $table->string('satuan')->default('LBR');
            $table->timestamps();

            $table->unique(['kode_barang', 'so']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('stocks');
    }
};