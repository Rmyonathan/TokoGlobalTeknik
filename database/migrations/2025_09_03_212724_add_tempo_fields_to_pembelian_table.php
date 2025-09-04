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
        Schema::table('pembelian', function (Blueprint $table) {
            $table->unsignedInteger('hari_tempo')->default(0)->after('cara_bayar');
            $table->date('tanggal_jatuh_tempo')->nullable()->after('hari_tempo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pembelian', function (Blueprint $table) {
            $table->dropColumn(['hari_tempo', 'tanggal_jatuh_tempo']);
        });
    }
};
