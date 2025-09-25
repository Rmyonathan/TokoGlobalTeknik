<?php

namespace Database\Seeders;

use App\Models\ReturPenjualan;
use App\Models\ReturPenjualanItem;
use App\Models\ReturPembelian;
use App\Models\ReturPembelianItem;
use App\Models\Transaksi;
use App\Models\Pembelian;
use App\Models\Customer;
use App\Models\Supplier;
use App\Models\KodeBarang;
use App\Services\PpnService;
use App\Http\Controllers\StockController;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ReturSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clean existing data
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        try { DB::table('retur_penjualan_items')->truncate(); } catch (\Throwable $e) {}
        try { DB::table('retur_penjualans')->truncate(); } catch (\Throwable $e) {}
        try { DB::table('retur_pembelian_items')->truncate(); } catch (\Throwable $e) {}
        try { DB::table('retur_pembelians')->truncate(); } catch (\Throwable $e) {}
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $transaksis = Transaksi::with('items')->get();
        $pembelians = Pembelian::with('items')->get();
        $customers = Customer::all();
        $suppliers = Supplier::all();

        if ($transaksis->isEmpty() || $pembelians->isEmpty()) {
            $this->command->warn('⚠️  Transaksi atau pembelian belum ada. Jalankan PenjualanSeeder dan PembelianSeeder terlebih dahulu.');
            return;
        }

        // Create Sales Returns (Retur Penjualan)
        $returPenjualanCount = 5;
        $returPenjualanData = [];

        for ($i = 1; $i <= $returPenjualanCount; $i++) {
            $transaksi = $transaksis->random();
            $customer = $customers->where('kode_customer', $transaksi->kode_customer)->first();
            
            if (!$customer) continue;

            $tanggal = now()->subDays(rand(1, 15));
            
            // Create sales return
            $returPenjualan = ReturPenjualan::create([
                'no_retur' => 'RET-SALE-' . now()->format('Ymd') . '-' . str_pad($i, 4, '0', STR_PAD_LEFT) . '-' . rand(1000, 9999),
                'tanggal' => $tanggal,
                'no_transaksi' => $transaksi->no_transaksi,
                'transaksi_id' => $transaksi->id,
                'kode_customer' => $transaksi->kode_customer,
                'alasan_retur' => 'Barang cacat/rusak',
                'status' => 'processed',
            ]);

            // Return 1-3 items from the transaction
            if ($transaksi->items->count() == 0) {
                continue; // Skip if no items
            }
            $itemsToReturn = $transaksi->items->random(rand(1, min(3, $transaksi->items->count())));
            $totalRetur = 0;

            foreach ($itemsToReturn as $transaksiItem) {
                // Return 20-80% of the original quantity
                $qtyRetur = round($transaksiItem->qty * (rand(20, 80) / 100));
                if ($qtyRetur <= 0) continue;

                $totalItemRetur = $qtyRetur * $transaksiItem->harga;

                // Create return item
                ReturPenjualanItem::create([
                    'retur_penjualan_id' => $returPenjualan->id,
                    'transaksi_item_id' => $transaksiItem->id,
                    'kode_barang' => $transaksiItem->kode_barang,
                    'nama_barang' => $transaksiItem->nama_barang,
                    'qty_retur' => $qtyRetur,
                    'harga' => $transaksiItem->harga,
                    'total' => $totalItemRetur,
                    'satuan' => 'PCS', // Default unit
                ]);

                $totalRetur += $totalItemRetur;

                // Update stock (return to inventory)
                $stock = \App\Models\Stock::where('kode_barang', $transaksiItem->kode_barang)->first();
                if ($stock) {
                    $stock->increment('good_stock', $qtyRetur);
                }

                // Record stock mutation for sales return (stock coming back in)
                $stockController = new StockController();
                $stockController->recordPurchase(
                    $transaksiItem->kode_barang,
                    $transaksiItem->nama_barang,
                    $returPenjualan->no_retur,
                    $returPenjualan->tanggal->format('Y-m-d H:i:s'),
                    $returPenjualan->no_retur,
                    $customer->nama . ' (Return)',
                    $qtyRetur,
                    'PCS',
                    'Sales return from ' . $customer->nama,
                    'SEEDER'
                );
            }

            // Update return totals
            $returPenjualan->update([
                'total_retur' => $totalRetur,
            ]);

            // Update customer credit
            $customer->increment('sisa_kredit', $totalRetur);

            $returPenjualanData[] = [
                'no_retur' => $returPenjualan->no_retur,
                'customer' => $customer->nama,
                'original_transaksi' => $transaksi->no_transaksi,
                'total_retur' => $totalRetur,
                'items' => $itemsToReturn->count(),
            ];
        }

        // Create Purchase Returns (Retur Pembelian)
        $returPembelianCount = 3;
        $returPembelianData = [];

        for ($i = 1; $i <= $returPembelianCount; $i++) {
            $pembelian = $pembelians->random();
            $supplier = $suppliers->where('kode_supplier', $pembelian->kode_supplier)->first();
            
            if (!$supplier) continue;

            $tanggal = now()->subDays(rand(1, 10));
            
            // Create purchase return
            $returPembelian = ReturPembelian::create([
                'no_retur' => 'RET-PUR-' . now()->format('Ymd') . '-' . str_pad($i, 4, '0', STR_PAD_LEFT) . '-' . rand(1000, 9999),
                'tanggal' => $tanggal,
                'no_pembelian' => $pembelian->nota,
                'pembelian_id' => $pembelian->id,
                'kode_supplier' => $pembelian->kode_supplier,
                'alasan_retur' => 'Barang tidak sesuai spesifikasi',
                'status' => 'processed',
            ]);

            // Return 1-2 items from the purchase
            if ($pembelian->items->count() == 0) {
                continue; // Skip if no items
            }
            $itemsToReturn = $pembelian->items->random(rand(1, min(2, $pembelian->items->count())));
            $totalRetur = 0;

            foreach ($itemsToReturn as $pembelianItem) {
                // Return 10-50% of the original quantity
                $qtyRetur = round($pembelianItem->qty * (rand(10, 50) / 100));
                if ($qtyRetur <= 0) continue;

                $totalItemRetur = $qtyRetur * $pembelianItem->harga;

                // Create return item
                ReturPembelianItem::create([
                    'retur_pembelian_id' => $returPembelian->id,
                    'pembelian_item_id' => $pembelianItem->id,
                    'kode_barang' => $pembelianItem->kode_barang,
                    'nama_barang' => $pembelianItem->nama_barang,
                    'qty_retur' => $qtyRetur,
                    'harga' => $pembelianItem->harga,
                    'total' => $totalItemRetur,
                    'satuan' => 'PCS', // Default unit
                ]);

                $totalRetur += $totalItemRetur;

                // Update stock (remove from inventory)
                $stock = \App\Models\Stock::where('kode_barang', $pembelianItem->kode_barang)->first();
                if ($stock) {
                    $stock->decrement('good_stock', $qtyRetur);
                }

                // Record stock mutation for purchase return (stock going out)
                $stockController = new StockController();
                $stockController->recordSale(
                    $pembelianItem->kode_barang,
                    $pembelianItem->nama_barang,
                    $returPembelian->no_retur,
                    $returPembelian->tanggal->format('Y-m-d H:i:s'),
                    $returPembelian->no_retur,
                    $supplier->nama . ' (Return)',
                    $qtyRetur,
                    'PCS',
                    'Purchase return to ' . $supplier->nama,
                    'SEEDER'
                );
            }

            // Update return totals
            $returPembelian->update([
                'total_retur' => $totalRetur,
            ]);

            $returPembelianData[] = [
                'no_retur' => $returPembelian->no_retur,
                'supplier' => $supplier->nama,
                'original_pembelian' => $pembelian->nota,
                'total_retur' => $totalRetur,
                'items' => $itemsToReturn->count(),
            ];
        }

        $this->command->info('✅ Data retur berhasil dibuat!');
        $this->command->info('Total retur penjualan: ' . $returPenjualanCount);
        $this->command->info('Total retur pembelian: ' . $returPembelianCount);
        $this->command->info('Total nilai retur penjualan: Rp ' . number_format(collect($returPenjualanData)->sum('total_retur'), 0, ',', '.'));
        $this->command->info('Total nilai retur pembelian: Rp ' . number_format(collect($returPembelianData)->sum('total_retur'), 0, ',', '.'));
        
        // Show sample data
        $this->command->info("\n📋 Sample sales returns:");
        foreach (array_slice($returPenjualanData, 0, 3) as $data) {
            $this->command->info("- {$data['no_retur']} | {$data['customer']} | Rp " . number_format($data['total_retur'], 0, ',', '.'));
        }
        
        $this->command->info("\n📋 Sample purchase returns:");
        foreach (array_slice($returPembelianData, 0, 3) as $data) {
            $this->command->info("- {$data['no_retur']} | {$data['supplier']} | Rp " . number_format($data['total_retur'], 0, ',', '.'));
        }
    }
}
