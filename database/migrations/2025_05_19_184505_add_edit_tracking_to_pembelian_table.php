<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEditTrackingToPembelianTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pembelian', function (Blueprint $table) {
            $table->boolean('is_edited')->default(false);
            $table->string('edited_by')->nullable();
            $table->timestamp('edited_at')->nullable();
            $table->string('edit_reason')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('pembelian', function (Blueprint $table) {
            $table->dropColumn(['is_edited', 'edited_by', 'edited_at', 'edit_reason']);
        });
    }
}