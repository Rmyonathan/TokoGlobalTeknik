<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kode_barangs', function (Blueprint $table) {
            $table->string('kode_barang')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('kode_barangs', function (Blueprint $table) {
            $table->string('kode_barang')->nullable(false)->change();
        });
    }
};


