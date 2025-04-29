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
        Schema::create('panels', function (Blueprint $table) {
            $table->id();
            // $table->string('group_id')->nullable();
            $table->string('group_id')->nullable();
            $table->string('name');
            $table->decimal('length', 8, 2); // Length in meters with 2 decimal precision
            $table->decimal('cost', 8, 2);
            $table->decimal('price', 8, 2);
            $table->boolean('available')->default(true);
            $table->unsignedBigInteger('parent_panel_id')->nullable(); // For tracking cut panels
            $table->timestamps();

            $table->foreign('parent_panel_id')->references('id')->on('panels')
                ->onDelete('set null');
            $table->foreign('group_id') // Kolom di tabel transaksi
            ->references('kode_barang') // Kolom di tabel stok_owners
            ->on('kode_barangs')
            ->onDelete('set null') // Hapus transaksi jika stok owner dihapus
            ->onUpdate('cascade'); // Update foreign key jika stok owner diubah
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('panels');
    }
};
