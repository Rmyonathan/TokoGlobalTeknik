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
        Schema::create('cara_bayars', function (Blueprint $table) {
            $table->id();
            $table->enum('metode', ['Tunai', 'Non Tunai']);
            $table->string('nama'); // contoh: "Cash", "Transfer Bank BCA xxxxxx"
            $table->timestamps();
        });        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cara_bayars');
    }
};
