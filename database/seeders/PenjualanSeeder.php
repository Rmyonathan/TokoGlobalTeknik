<?php

namespace Database\Seeders;

use App\Models\Transaksi;
use App\Models\TransaksiItem;
use App\Models\TransaksiItemSumber;
use App\Models\Customer;
use App\Models\KodeBarang;
use App\Models\StockBatch;
use App\Services\PpnService;
use App\Services\FifoService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PenjualanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clean existing data
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        try { DB::table('transaksi_item_sumbers')->truncate(); } catch (\Throwable $e) {}
        try { DB::table('transaksi_items')->truncate(); } catch (\Throwable $e) {}
        try { DB::table('transaksis')->truncate(); } catch (\Throwable $e) {}
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $customers = Customer::all();
        $barangs = KodeBarang::all();
        $fifoService = new FifoService();

        if ($customers->isEmpty() || $barangs->isEmpty()) {
            $this->command->warn('⚠️  Customer atau barang belum ada. Jalankan CustomerSupplierSeeder dan BarangSeeder terlebih dahulu.');
            return;
        }

        // Create sales transactions
        $transaksiCount = 20; // Create 20 sales transactions
        $transaksiData = [];

        for ($i = 1; $i <= $transaksiCount; $i++) {
            $customer = $customers->random();
            $caraBayar = ['Tunai', 'Kredit'][rand(0, 1)];
            $pembayaran = $caraBayar === 'Tunai' ? 'Tunai' : 'Non Tunai';
            
            // Random date within last 30 days
            $tanggal = now()->subDays(rand(0, 30));
            
            // Create unique transaction number
            $noTransaksi = 'SALE-' . now()->format('Ymd') . '-' . str_pad($i, 4, '0', STR_PAD_LEFT) . '-' . rand(1000, 9999);
            
            // Create transaction
            $transaksi = Transaksi::create([
                'no_transaksi' => $noTransaksi,
                'tanggal' => $tanggal,
                'kode_customer' => $customer->kode_customer,
                'cara_bayar' => $caraBayar,
                'pembayaran' => $pembayaran,
                'sales' => 'SO001',
                'status' => 'completed',
                'status_piutang' => $caraBayar === 'Kredit' ? 'belum_dibayar' : 'lunas',
                'subtotal' => 0, // Will be updated later
                'discount' => 0,
                'disc_rupiah' => 0,
                'ppn' => 0,
                'dp' => 0,
                'grand_total' => 0, // Will be updated later
                'total_dibayar' => 0,
                'sisa_piutang' => 0,
            ]);

            // Add 1-5 items per transaction
            $itemCount = rand(1, 5);
            $subtotal = 0;
            $totalCogs = 0;

            for ($j = 1; $j <= $itemCount; $j++) {
                $barang = $barangs->random();
                $qty = rand(1, 10);
                $hargaJual = $barang->harga_jual;
                $total = $qty * $hargaJual;
                $subtotal += $total;

                // Create transaction item
                $transaksiItem = TransaksiItem::create([
                    'transaksi_id' => $transaksi->id,
                    'no_transaksi' => $transaksi->no_transaksi,
                    'kode_barang' => $barang->kode_barang,
                    'nama_barang' => $barang->name,
                    'qty' => $qty,
                    'harga' => $hargaJual,
                    'total' => $total,
                ]);

                // Allocate stock using FIFO
                try {
                    $alokasiResult = $fifoService->alokasiStok($barang->id, $qty, $transaksiItem->id);
                    
                    // Calculate COGS from allocation
                    if ($alokasiResult['success'] && isset($alokasiResult['alokasi'])) {
                        foreach ($alokasiResult['alokasi'] as $alokasi) {
                            $totalCogs += $alokasi['qty_ambil'] * $alokasi['harga_modal'];
                        }
                    }
                } catch (\Exception $e) {
                    $this->command->warn("FIFO allocation failed for item {$barang->kode_barang}: " . $e->getMessage());
                }
            }

            // Calculate PPN and totals
            $ppnCalculation = PpnService::calculateGrandTotal($subtotal, 0, 0);
            $grandTotal = $ppnCalculation['grand_total'];
            $ppn = $ppnCalculation['ppn'];

            // Update transaction totals
            $transaksi->update([
                'subtotal' => $subtotal,
                'ppn' => $ppn,
                'grand_total' => $grandTotal,
                'total_dibayar' => $caraBayar === 'Tunai' ? $grandTotal : 0,
                'sisa_piutang' => $caraBayar === 'Kredit' ? $grandTotal : 0,
            ]);

            // Update customer credit
            if ($caraBayar === 'Kredit') {
                $customer->decrement('sisa_kredit', $grandTotal);
            }

            $transaksiData[] = [
                'no_transaksi' => $transaksi->no_transaksi,
                'customer' => $customer->nama_customer,
                'cara_bayar' => $caraBayar,
                'subtotal' => $subtotal,
                'ppn' => $ppn,
                'grand_total' => $grandTotal,
                'items' => $itemCount,
                'cogs' => $totalCogs,
            ];
        }

        $this->command->info('✅ Data penjualan berhasil dibuat!');
        $this->command->info('Total transaksi: ' . $transaksiCount);
        $this->command->info('Total revenue: Rp ' . number_format(collect($transaksiData)->sum('grand_total'), 0, ',', '.'));
        $this->command->info('Total COGS: Rp ' . number_format(collect($transaksiData)->sum('cogs'), 0, ',', '.'));
        
        // Show sample data
        $this->command->info("\n📋 Sample transactions:");
        foreach (array_slice($transaksiData, 0, 5) as $data) {
            $this->command->info("- {$data['no_transaksi']} | {$data['customer']} | {$data['cara_bayar']} | Rp " . number_format($data['grand_total'], 0, ',', '.'));
        }
    }
}
