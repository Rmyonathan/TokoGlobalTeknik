<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('stock_transfers', function (Blueprint $table) {
            $table->id();
            $table->string('no_transfer')->unique();
            $table->date('tanggal_transfer');
            $table->string('from_database'); // primary, secondary
            $table->string('to_database');   // primary, secondary
            $table->text('keterangan')->nullable();
            $table->enum('status', ['pending', 'approved', 'completed', 'cancelled'])->default('pending');
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->timestamps();

            $table->index(['from_database', 'to_database']);
            $table->index(['status', 'tanggal_transfer']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('stock_transfers');
    }
};