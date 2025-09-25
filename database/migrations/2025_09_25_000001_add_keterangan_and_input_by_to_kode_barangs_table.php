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
        Schema::table('kode_barangs', function (Blueprint $table) {
            if (!Schema::hasColumn('kode_barangs', 'keterangan')) {
                $table->text('keterangan')->nullable()->after('ongkos_kuli_default');
            }
            if (!Schema::hasColumn('kode_barangs', 'input_by')) {
                $table->string('input_by', 100)->nullable()->after('keterangan');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kode_barangs', function (Blueprint $table) {
            if (Schema::hasColumn('kode_barangs', 'input_by')) {
                $table->dropColumn('input_by');
            }
            if (Schema::hasColumn('kode_barangs', 'keterangan')) {
                $table->dropColumn('keterangan');
            }
        });
    }
};


