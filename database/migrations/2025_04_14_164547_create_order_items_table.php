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
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('order_id');
            $table->unsignedBigInteger('panel_id');
            $table->decimal('length', 8, 2); // Requested length
            $table->decimal('original_panel_length', 8, 2); // Original panel length
            $table->decimal('remaining_length', 8, 2); // What's left after cutting
            $table->decimal('transaction', 8, 2); // What's left after cutting
            $table->timestamps();

            $table->foreign('order_id')->references('id')->on('orders')
                ->onDelete('cascade');
            $table->foreign('panel_id')->references('id')->on('panels')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
