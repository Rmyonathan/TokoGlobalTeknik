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
        Schema::table('nota_debit', function (Blueprint $table) {
            $table->decimal('sisa_nota_debit', 15, 2)->default(0)->after('total_debit');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('nota_debit', function (Blueprint $table) {
            $table->dropColumn('sisa_nota_debit');
        });
    }
};
