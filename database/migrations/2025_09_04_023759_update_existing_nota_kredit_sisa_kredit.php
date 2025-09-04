<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update existing nota kredit records to set sisa_kredit = total_kredit
        DB::statement('UPDATE nota_kredit SET sisa_kredit = total_kredit WHERE sisa_kredit = 0 OR sisa_kredit IS NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This migration cannot be reversed as it updates data
    }
};
