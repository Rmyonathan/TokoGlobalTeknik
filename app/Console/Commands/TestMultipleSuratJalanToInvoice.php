<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SuratJalan;
use App\Models\SuratJalanItem;
use App\Models\SuratJalanItemSumber;
use App\Models\Transaksi;
use App\Models\TransaksiItem;
use App\Models\TransaksiItemSumber;
use App\Models\Customer;
use App\Models\KodeBarang;
use App\Models\StockBatch;
use App\Http\Controllers\MultipleSuratJalanController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TestMultipleSuratJalanToInvoice extends Command
{
    protected $signature = 'test:multiple-sj-to-invoice';
    protected $description = 'Test creating invoice from multiple delivery orders';

    public function handle()
    {
        $this->info('Testing Multiple Surat Jalan to Invoice feature...');

        try {
            DB::beginTransaction();

            // 1. Create test customer if not exists
            $customer = Customer::firstOrCreate(
                ['kode_customer' => 'TEST-CUST-001'],
                [
                    'nama' => 'Test Customer Multiple SJ',
                    'alamat' => 'Test Address',
                    'no_telp' => '081234567890',
                    'email' => 'test@example.com'
                ]
            );
            $this->info("✓ Customer created/found: {$customer->nama}");

            // 2. Create test kode barang if not exists
            $kodeBarang = KodeBarang::firstOrCreate(
                ['kode_barang' => 'TEST-BRG-001'],
                [
                    'nama_barang' => 'Test Barang Multiple SJ',
                    'satuan' => 'PCS',
                    'cost' => 10000,
                    'harga_jual' => 15000
                ]
            );
            $this->info("✓ Kode Barang created/found: {$kodeBarang->nama_barang}");

            // 3. Create test stock batch
            $stockBatch = StockBatch::create([
                'kode_barang_id' => $kodeBarang->id,
                'kode_barang' => $kodeBarang->kode_barang,
                'qty_masuk' => 100,
                'qty_sisa' => 100,
                'harga_beli' => 10000,
                'tanggal_masuk' => now(),
                'supplier' => 'Test Supplier'
            ]);
            $this->info("✓ Stock Batch created: {$stockBatch->qty_masuk} units");

            // 4. Create first Surat Jalan
            $sj1 = SuratJalan::create([
                'no_suratjalan' => 'SJ-TEST-001',
                'tanggal' => now()->subDays(2),
                'kode_customer' => $customer->kode_customer,
                'alamat_suratjalan' => 'Test Address 1'
            ]);

            $sjItem1 = SuratJalanItem::create([
                'no_suratjalan' => $sj1->no_suratjalan,
                'kode_barang' => $kodeBarang->kode_barang,
                'nama_barang' => $kodeBarang->nama_barang,
                'qty' => 5,
                'satuan' => 'PCS'
            ]);

            // Create FIFO allocation for SJ1
            SuratJalanItemSumber::create([
                'surat_jalan_item_id' => $sjItem1->id,
                'stock_batch_id' => $stockBatch->id,
                'qty_diambil' => 5,
                'harga_modal' => 10000
            ]);

            $this->info("✓ Surat Jalan 1 created: {$sj1->no_suratjalan}");

            // 5. Create second Surat Jalan
            $sj2 = SuratJalan::create([
                'no_suratjalan' => 'SJ-TEST-002',
                'tanggal' => now()->subDays(1),
                'kode_customer' => $customer->kode_customer,
                'alamat_suratjalan' => 'Test Address 2'
            ]);

            $sjItem2 = SuratJalanItem::create([
                'no_suratjalan' => $sj2->no_suratjalan,
                'kode_barang' => $kodeBarang->kode_barang,
                'nama_barang' => $kodeBarang->nama_barang,
                'qty' => 3,
                'satuan' => 'PCS'
            ]);

            // Create FIFO allocation for SJ2
            SuratJalanItemSumber::create([
                'surat_jalan_item_id' => $sjItem2->id,
                'stock_batch_id' => $stockBatch->id,
                'qty_diambil' => 3,
                'harga_modal' => 10000
            ]);

            $this->info("✓ Surat Jalan 2 created: {$sj2->no_suratjalan}");

            // 6. Test the multiple SJ to invoice feature
            $controller = new MultipleSuratJalanController();
            
            $request = new Request([
                'kode_customer' => $customer->kode_customer,
                'surat_jalan_ids' => [$sj1->id, $sj2->id],
                'tanggal' => now()->format('Y-m-d'),
                'pembayaran' => 'Non Tunai',
                'cara_bayar' => 'Transfer',
                'hari_tempo' => 30,
                'tanggal_jatuh_tempo' => now()->addDays(30)->format('Y-m-d'),
                'merge_similar_items' => true
            ]);

            $response = $controller->store($request);
            $responseData = json_decode($response->getContent(), true);

            if ($responseData['success']) {
                $this->info("✓ Invoice created successfully!");
                $this->info("  - Invoice ID: {$responseData['transaksi_id']}");
                $this->info("  - Invoice Number: {$responseData['no_transaksi']}");
                
                // Verify the invoice
                $transaksi = Transaksi::find($responseData['transaksi_id']);
                $this->info("  - Customer: {$transaksi->kode_customer}");
                $this->info("  - Total Items: {$transaksi->items->count()}");
                $this->info("  - Grand Total: " . number_format($transaksi->grand_total, 2));
                
                // Verify FIFO transfer
                $transaksiItems = $transaksi->items;
                foreach ($transaksiItems as $item) {
                    $this->info("  - Item: {$item->nama_barang} (Qty: {$item->qty})");
                    $sumberCount = $item->transaksiItemSumber->count();
                    $this->info("    - FIFO Sources: {$sumberCount}");
                    
                    foreach ($item->transaksiItemSumber as $sumber) {
                        $this->info("      - Batch ID: {$sumber->stock_batch_id}, Qty: {$sumber->qty_diambil}, Price: {$sumber->harga_modal}");
                    }
                }
                
                // Verify Surat Jalan updates
                $sj1->refresh();
                $sj2->refresh();
                $this->info("  - SJ1 linked to invoice: " . ($sj1->no_transaksi ? 'Yes' : 'No'));
                $this->info("  - SJ2 linked to invoice: " . ($sj2->no_transaksi ? 'Yes' : 'No'));
                
            } else {
                $this->error("✗ Failed to create invoice: " . $responseData['message']);
            }

            DB::commit();
            $this->info("✓ Test completed successfully!");

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("✗ Test failed: " . $e->getMessage());
            $this->error("Stack trace: " . $e->getTraceAsString());
        }
    }
}