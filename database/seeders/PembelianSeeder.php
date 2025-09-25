<?php

namespace Database\Seeders;

use App\Models\Pembelian;
use App\Models\PembelianItem;
use App\Models\Supplier;
use App\Models\KodeBarang;
use App\Models\StockBatch;
use App\Services\PpnService;
use App\Http\Controllers\StockController;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PembelianSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clean existing data
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        try { DB::table('pembelian_items')->truncate(); } catch (\Throwable $e) {}
        try { DB::table('pembelians')->truncate(); } catch (\Throwable $e) {}
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $suppliers = Supplier::all();
        $barangs = KodeBarang::all();

        if ($suppliers->isEmpty() || $barangs->isEmpty()) {
            $this->command->warn('⚠️  Supplier atau barang belum ada. Jalankan CustomerSupplierSeeder dan BarangSeeder terlebih dahulu.');
            return;
        }

        // Create purchase transactions
        $pembelianCount = 15; // Create 15 purchase transactions
        $pembelianData = [];

        for ($i = 1; $i <= $pembelianCount; $i++) {
            $supplier = $suppliers->random();
            $caraBayar = ['Tunai', 'Kredit'][rand(0, 1)];
            $pembayaran = $caraBayar === 'Tunai' ? 'Tunai' : 'Non Tunai';
            
            // Random date within last 30 days
            $tanggal = now()->subDays(rand(0, 30));
            
            // Create unique nota number
            $nota = 'PUR-' . now()->format('Ymd') . '-' . str_pad($i, 4, '0', STR_PAD_LEFT) . '-' . rand(1000, 9999);
            
            // Create purchase
            $pembelian = Pembelian::create([
                'nota' => $nota,
                'tanggal' => $tanggal,
                'kode_supplier' => $supplier->kode_supplier,
                'cara_bayar' => $caraBayar,
                'pembayaran' => $pembayaran,
                'status' => 'completed',
                'subtotal' => 0, // Will be updated later
                'diskon' => 0,
                'ppn' => 0,
                'grand_total' => 0, // Will be updated later
                'total_dibayar' => 0,
                'sisa_utang' => 0,
                'status_utang' => 'belum_dibayar',
            ]);

            // Add 1-4 items per purchase
            $itemCount = rand(1, 4);
            $subtotal = 0;

            for ($j = 1; $j <= $itemCount; $j++) {
                $barang = $barangs->random();
                $qty = rand(5, 50);
                
                // Purchase price slightly higher than selling price for margin
                $hargaBeli = $barang->cost * (1 + rand(5, 15) / 100);
                $total = $qty * $hargaBeli;
                $subtotal += $total;

                // Create purchase item
                PembelianItem::create([
                    'nota' => $pembelian->nota,
                    'kode_barang' => $barang->kode_barang,
                    'nama_barang' => $barang->name,
                    'qty' => $qty,
                    'harga' => $hargaBeli,
                    'total' => $total,
                ]);

                // Create stock batch for FIFO
                StockBatch::create([
                    'kode_barang_id' => $barang->id,
                    'qty_masuk' => $qty,
                    'qty_sisa' => $qty,
                    'harga_beli' => $hargaBeli,
                    'tanggal_masuk' => $tanggal,
                    'batch_number' => 'BATCH-' . $pembelian->nota . '-' . $j,
                    'keterangan' => $supplier->nama,
                ]);

                // Record stock mutation for purchase
                $stockController = new StockController();
                $stockController->recordPurchase(
                    $barang->kode_barang,
                    $barang->name,
                    $pembelian->nota,
                    $tanggal->format('Y-m-d H:i:s'),
                    $pembelian->nota,
                    $supplier->nama,
                    $qty,
                    $barang->unit_dasar,
                    'Purchase from ' . $supplier->nama,
                    'SEEDER'
                );

                // Update stock
                $stock = \App\Models\Stock::where('kode_barang', $barang->kode_barang)->first();
                if ($stock) {
                    $stock->increment('good_stock', $qty);
                }
            }

            // Calculate PPN and totals
            $ppnCalculation = PpnService::calculateGrandTotal($subtotal, 0, 0);
            $grandTotal = $ppnCalculation['grand_total'];
            $ppn = $ppnCalculation['ppn'];

            // Update purchase totals
            $pembelian->update([
                'subtotal' => $subtotal,
                'ppn' => $ppn,
                'grand_total' => $grandTotal,
            ]);

            $pembelianData[] = [
                'nota' => $pembelian->nota,
                'supplier' => $supplier->nama,
                'cara_bayar' => $caraBayar,
                'subtotal' => $subtotal,
                'ppn' => $ppn,
                'grand_total' => $grandTotal,
                'items' => $itemCount,
            ];
        }

        $this->command->info('✅ Data pembelian berhasil dibuat!');
        $this->command->info('Total pembelian: ' . $pembelianCount);
        $this->command->info('Total cost: Rp ' . number_format(collect($pembelianData)->sum('grand_total'), 0, ',', '.'));
        
        // Show sample data
        $this->command->info("\n📋 Sample purchases:");
        foreach (array_slice($pembelianData, 0, 5) as $data) {
            $this->command->info("- {$data['nota']} | {$data['supplier']} | {$data['cara_bayar']} | Rp " . number_format($data['grand_total'], 0, ',', '.'));
        }
    }
}
