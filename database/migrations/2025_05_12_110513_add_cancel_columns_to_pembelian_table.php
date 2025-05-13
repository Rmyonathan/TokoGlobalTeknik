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
            $table->string('status')->default('active')->after('grand_total'); // 'active' or 'canceled'
            $table->string('canceled_by')->nullable()->after('status');
            $table->timestamp('canceled_at')->nullable()->after('canceled_by');
            $table->text('cancel_reason')->nullable()->after('canceled_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pembelian', function (Blueprint $table) {
            $table->dropColumn(['status', 'canceled_by', 'canceled_at', 'cancel_reason']);
        });
    }
};