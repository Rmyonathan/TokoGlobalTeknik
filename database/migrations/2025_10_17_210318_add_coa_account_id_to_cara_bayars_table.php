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
        Schema::table('cara_bayars', function (Blueprint $table) {
            $table->foreignId('coa_account_id')->nullable()->after('nama')
                ->constrained('chart_of_accounts')->onDelete('set null');
            $table->index('coa_account_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cara_bayars', function (Blueprint $table) {
            $table->dropForeign(['coa_account_id']);
            $table->dropIndex(['coa_account_id']);
            $table->dropColumn('coa_account_id');
        });
    }
};
