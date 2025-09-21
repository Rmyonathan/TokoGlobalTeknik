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
        Schema::create('stock_transfer_logs', function (Blueprint $table) {
            $table->id();
            $table->string('transfer_no')->index();
            $table->string('kode_barang')->index();
            $table->unsignedBigInteger('kode_barang_id');
            $table->decimal('qty', 15, 2);
            $table->decimal('avg_cost', 15, 2);
            $table->string('unit', 10)->default('PCS');
            $table->string('source_db');
            $table->string('target_db');
            $table->enum('role', ['source', 'target']);
            $table->string('created_by')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();
            
            $table->index(['transfer_no', 'role']);
            $table->index(['kode_barang', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_transfer_logs');
    }
};