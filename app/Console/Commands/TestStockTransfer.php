<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Stock;
use App\Models\KodeBarang;
use App\Services\GlobalStockService;

class TestStockTransfer extends Command
{
    protected $signature = 'test:stock-transfer {kode_barang}';
    protected $description = 'Test stock transfer functionality';

    public function handle()
    {
        $kodeBarang = $this->argument('kode_barang');
        
        $this->info("Testing Stock Transfer for: {$kodeBarang}");
        $this->line('');

        // Test global stock
        $this->info('1. Testing Global Stock...');
        $globalStock = Stock::getGlobalStock($kodeBarang);
        $this->line("   Global Stock: {$globalStock->good_stock} good, {$globalStock->bad_stock} bad");
        
        // Test stock breakdown
        $this->info('2. Testing Stock Breakdown...');
        $breakdown = Stock::getStockBreakdown($kodeBarang);
        foreach ($breakdown as $db => $data) {
            $this->line("   {$db}: {$data['good_stock']} good, {$data['bad_stock']} bad");
        }
        
        // Test stock transfer
        $this->info('3. Testing Stock Transfer...');
        try {
            $result = Stock::transferStock($kodeBarang, 10, 'primary', 'secondary', 10000);
            if ($result) {
                $this->info('   Transfer successful!');
            }
        } catch (\Exception $e) {
            $this->error('   Transfer failed: ' . $e->getMessage());
        }
        
        // Test global stock after transfer
        $this->info('4. Testing Global Stock After Transfer...');
        $globalStockAfter = Stock::getGlobalStock($kodeBarang);
        $this->line("   Global Stock: {$globalStockAfter->good_stock} good, {$globalStockAfter->bad_stock} bad");
        
        $this->info('Test completed!');
    }
}