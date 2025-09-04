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
            $table->decimal('total_dibayar', 15, 2)->default(0)->after('grand_total');
            $table->decimal('sisa_utang', 15, 2)->default(0)->after('total_dibayar');
            $table->enum('status_utang', ['belum_dibayar', 'sebagian', 'lunas'])->default('belum_dibayar')->after('sisa_utang');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pembelian', function (Blueprint $table) {
            $table->dropColumn(['total_dibayar', 'sisa_utang', 'status_utang']);
        });
    }
};
