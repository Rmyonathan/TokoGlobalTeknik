<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('pembayaran_piutang_nota_kredits')) {
            Schema::table('pembayaran_piutang_nota_kredits', function (Blueprint $table) {
                // Add foreign keys (DBAL may be needed to check existing FKs; assume not present)
                try {
                    $table->foreign('pembayaran_id', 'fk_pp_nk_pembayaran_id')
                        ->references('id')->on('pembayarans')
                        ->onDelete('cascade');
                } catch (\Throwable $e) {}

                try {
                    $table->foreign('nota_kredit_id', 'fk_pp_nk_nota_kredit_id')
                        ->references('id')->on('nota_kredit')
                        ->onDelete('cascade');
                } catch (\Throwable $e) {}
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('pembayaran_piutang_nota_kredits')) {
            Schema::table('pembayaran_piutang_nota_kredits', function (Blueprint $table) {
                try { $table->dropForeign('fk_pp_nk_pembayaran_id'); } catch (\Throwable $e) {}
                try { $table->dropForeign('fk_pp_nk_nota_kredit_id'); } catch (\Throwable $e) {}
            });
        }
    }
};
