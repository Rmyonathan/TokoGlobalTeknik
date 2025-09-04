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
        Schema::table('customers', function (Blueprint $table) {
            // Tambahkan kolom untuk menyimpan sisa kredit customer
            $table->decimal('sisa_kredit', 15, 2)->default(0)->after('limit_kredit'); // Sisa kredit yang tersedia
            $table->decimal('total_piutang', 15, 2)->default(0)->after('sisa_kredit'); // Total piutang customer
            
            // Index untuk optimasi query
            $table->index(['sisa_kredit']);
            $table->index(['total_piutang']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropIndex(['sisa_kredit']);
            $table->dropIndex(['total_piutang']);
            $table->dropColumn([
                'sisa_kredit',
                'total_piutang'
            ]);
        });
    }
};