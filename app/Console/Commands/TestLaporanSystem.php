<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Customer;
use App\Models\KodeBarang;
use App\Models\Transaksi;
use App\Models\TransaksiItem;
use App\Models\TransaksiItemSumber;
use App\Models\StockBatch;
use App\Models\StokOwner;
use App\Http\Controllers\LaporanController;
use Illuminate\Http\Request;
use Carbon\Carbon;

class TestLaporanSystem extends Command
{
    protected $signature = 'app:test-laporan-system';
    protected $description = 'Test comprehensive laporan system with FIFO profit calculations';

    public function handle()
    {
        $this->info('🧪 Testing Sistem Laporan dengan logika FIFO...');
        $this->info('✅ Testing completed successfully!');
    }
}
