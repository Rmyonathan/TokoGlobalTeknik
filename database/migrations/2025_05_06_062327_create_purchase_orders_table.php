<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePurchaseOrdersTable extends Migration
{
    public function up()
    {
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->string('no_po')->unique();
            $table->date('tanggal');
            $table->string('kode_customer');
            $table->string('sales')->nullable();
            $table->string('pembayaran')->nullable();
            $table->string('cara_bayar')->nullable();
            $table->date('tanggal_jadi')->nullable(); // NULL dulu bro
            $table->double('subtotal')->default(0);
            $table->double('discount')->default(0);
            $table->double('disc_rupiah')->default(0);
            $table->double('ppn')->default(0);
            $table->double('dp')->default(0);
            $table->double('grand_total')->default(0);
            $table->enum('status', ['pending', 'completed', 'edited'])->default('pending');
            
            // Add edit tracking fields
            $table->boolean('is_edited')->default(false);
            $table->string('edited_by')->nullable();
            $table->timestamp('edited_at')->nullable();
            $table->string('edit_reason')->nullable();
            
            $table->timestamps();
        });
        
        // Also update the transaksi table to include the tracking fields
        Schema::table('transaksi', function (Blueprint $table) {
            // Add only if the table exists
            if (Schema::hasTable('transaksi')) {
                // First check if the columns don't already exist
                if (!Schema::hasColumn('transaksi', 'created_from_po')) {
                    $table->string('created_from_po')->nullable()->after('status');
                    $table->boolean('is_edited')->default(false)->after('created_from_po');
                    $table->string('edited_by')->nullable()->after('is_edited');
                    $table->timestamp('edited_at')->nullable()->after('edited_by');
                    $table->string('edit_reason')->nullable()->after('edited_at');
                }
            }
        });
    }

    public function down()
    {
        Schema::dropIfExists('purchase_orders');
        
        // Remove the columns from transaksi table if it exists
        if (Schema::hasTable('transaksi')) {
            Schema::table('transaksi', function (Blueprint $table) {
                if (Schema::hasColumn('transaksi', 'created_from_po')) {
                    $table->dropColumn('created_from_po');
                }
                if (Schema::hasColumn('transaksi', 'is_edited')) {
                    $table->dropColumn('is_edited');
                }
                if (Schema::hasColumn('transaksi', 'edited_by')) {
                    $table->dropColumn('edited_by');
                }
                if (Schema::hasColumn('transaksi', 'edited_at')) {
                    $table->dropColumn('edited_at');
                }
                if (Schema::hasColumn('transaksi', 'edit_reason')) {
                    $table->dropColumn('edit_reason');
                }
            });
        }
    }
}
