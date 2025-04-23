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
        Schema::create('saldos', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('saldo')->nullable(); // Jika bisa menyimpan angka besar
            $table->integer('room_rate')->nullable(); // Jika hanya bilangan bulat
            $table->decimal('tax', 8, 2)->nullable(); // Menentukan jumlah digit dan skala desimal
            $table->bigInteger('hutang')->nullable(); // Jika hanya bilangan bulat
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('saldo');
    }
};