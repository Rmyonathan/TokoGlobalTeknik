<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kode_barangs', function (Blueprint $table) {
            if (!Schema::hasColumn('kode_barangs', 'min_stock')) {
                $table->unsignedInteger('min_stock')->nullable()->after('ongkos_kuli_default');
            }
        });
    }

    public function down(): void
    {
        Schema::table('kode_barangs', function (Blueprint $table) {
            if (Schema::hasColumn('kode_barangs', 'min_stock')) {
                $table->dropColumn('min_stock');
            }
        });
    }
};


