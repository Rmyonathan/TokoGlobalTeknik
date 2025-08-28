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
        Schema::table('transaksi_items', function (Blueprint $table) {
            $table->decimal('ongkos_kuli', 15, 2)->default(0)->after('total'); // Ongkos kuli per item
            $table->string('satuan', 20)->default('LBR')->after('qty'); // Satuan untuk item
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transaksi_items', function (Blueprint $table) {
            $table->dropColumn(['ongkos_kuli', 'satuan']);
        });
    }
};
