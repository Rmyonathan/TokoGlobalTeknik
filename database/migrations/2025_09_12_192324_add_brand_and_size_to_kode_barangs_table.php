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
            $table->string('merek', 255)->nullable()->after('attribute');
            $table->string('ukuran', 100)->nullable()->after('merek');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kode_barangs', function (Blueprint $table) {
            $table->dropColumn(['merek', 'ukuran']);
        });
    }
};
