<?php
// database/migrations/2025_05_15_ubah_status_purchase_orders_to_string.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterPOTable extends Migration
{
    public function up()
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->string('status', 32)->default('pending')->change();
        });
    }

    public function down()
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->enum('status', ['pending', 'completed'])->default('pending')->change();
        });
    }
}