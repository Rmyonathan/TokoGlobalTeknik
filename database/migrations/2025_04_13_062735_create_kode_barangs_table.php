<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('kode_barangs', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('cost', 8, 2);
            $table->decimal('price', 8, 2);
            $table->string('kode_barang')->unique();
            $table->string('attribute');
            $table->decimal('length', 8, 2);
            $table->enum('status', ['Active', 'Inactive']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kode_barangs');
    }
};
