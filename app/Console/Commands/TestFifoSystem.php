<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\FifoService;
use App\Models\StockBatch;
use App\Models\KodeBarang;
use App\Models\TransaksiItemSumber;

class TestFifoSystem extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:test-fifo-system';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test sistem FIFO untuk memastikan alokasi stok berjalan dengan benar';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🧪 Testing Sistem FIFO...');
        
        $fifoService = new FifoService();
        
        // Test 1: Cek stok tersedia
        $this->info('1. Mengecek stok tersedia...');
        $kodeBarangs = KodeBarang::take(5)->get();
        
        foreach ($kodeBarangs as $kodeBarang) {
            $stokTersedia = $fifoService->getStokTersedia($kodeBarang->id);
            $this->line("   - {$kodeBarang->kode_barang}: {$stokTersedia} unit");
        }
        
        // Test 2: Cek batch detail
        $this->info('2. Mengecek detail batch...');
        if ($kodeBarangs->count() > 0) {
            $batchDetail = $fifoService->getBatchDetail($kodeBarangs->first()->id);
            $this->line("   - Batch untuk {$kodeBarangs->first()->kode_barang}: {$batchDetail->count()} batch");
            
            foreach ($batchDetail as $batch) {
                $supplierName = $batch->pembelianItem->pembelian->supplierRelation->nama ?? 'Unknown';
                $this->line("     * Batch {$batch->batch_number}: {$batch->qty_sisa}/{$batch->qty_masuk} (Harga: Rp " . number_format($batch->harga_beli, 2) . ") - Supplier: {$supplierName}");
            }
        }
        
        // Test 3: Cek rata-rata harga modal
        $this->info('3. Mengecek rata-rata harga modal...');
        foreach ($kodeBarangs as $kodeBarang) {
            $avgHarga = $fifoService->hitungRataRataHargaModal($kodeBarang->id);
            $this->line("   - {$kodeBarang->kode_barang}: Rp " . number_format($avgHarga, 2));
        }
        
        $this->info('✅ Testing selesai!');
    }
}
