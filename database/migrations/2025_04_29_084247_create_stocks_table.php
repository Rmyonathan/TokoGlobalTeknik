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
            $table->string('kode_barang')->unique(); // Make kode_barang unique on its own
            $table->string('nama_barang');
            $table->decimal('good_stock', 12, 2)->default(0);
            $table->decimal('bad_stock', 12, 2)->default(0);
            $table->string('so')->default('default'); // Keep SO but with default value
            $table->string('satuan')->default('LBR');
            $table->timestamps();
            
            // Removed the unique constraint on ['kode_barang', 'so']
        });
    }

    public function down()
    {
        Schema::dropIfExists('stocks');
    }
};