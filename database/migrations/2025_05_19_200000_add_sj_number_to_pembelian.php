<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSjNumberToPembelian extends Migration
{
    
    public function up()
    {
        Schema::table('pembelian', function (Blueprint $table) {
            if (!Schema::hasColumn('pembelian', 'no_surat_jalan')) {
                $table->string('no_surat_jalan')->nullable()->after('nota');
            }
        });
    }


    public function down()
    {
        Schema::table('pembelian', function (Blueprint $table) {
            if (Schema::hasColumn('pembelian', 'no_surat_jalan')) {
                $table->dropColumn('no_surat_jalan');
            }
        });
    }
}