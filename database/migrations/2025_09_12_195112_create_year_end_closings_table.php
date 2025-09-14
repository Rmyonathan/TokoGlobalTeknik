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
        Schema::create('year_end_closings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('accounting_period_id');
            $table->year('fiscal_year');
            $table->date('closed_on')->nullable();
            $table->string('closed_by')->nullable();
            $table->enum('status', ['draft','closed','reopened'])->default('draft');
            $table->json('metadata')->nullable();
            $table->json('snapshots')->nullable();
            $table->timestamps();

            $table->foreign('accounting_period_id')
                ->references('id')
                ->on('accounting_periods')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('year_end_closings');
    }
};
