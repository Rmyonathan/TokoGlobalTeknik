<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\FifoService;
use App\Models\StockBatch;
use App\Models\KodeBarang;
use App\Models\TransaksiItemSumber;
use App\Models\TransaksiItem;
use App\Models\Transaksi;
use App\Models\Customer;
use App\Models\StokOwner;

class TestFifoSale extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:test-fifo-sale {qty=30}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test simulasi penjualan dengan sistem FIFO';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $qtyDijual = (int) $this->argument('qty');
        $this->info("🧪 Testing Simulasi Penjualan FIFO ({$qtyDijual} unit)...");
        
        $fifoService = new FifoService();
        
        // Ambil kode barang test
        $kodeBarang = KodeBarang::where('kode_barang', 'KB001')->first();
        if (!$kodeBarang) {
            $this->error('Kode barang KB001 tidak ditemukan! Jalankan FifoTestSeeder terlebih dahulu.');
            return;
        }

        // Cek stok sebelum penjualan
        $stokSebelum = $fifoService->getStokTersedia($kodeBarang->id);
        $this->info("1. Stok sebelum penjualan: {$stokSebelum} unit");

        if ($stokSebelum < $qtyDijual) {
            $this->error("Stok tidak mencukupi! Tersedia: {$stokSebelum}, Dibutuhkan: {$qtyDijual}");
            return;
        }

        // Buat stok owner dan customer untuk testing
        $stokOwner = StokOwner::firstOrCreate(
            ['kode_stok_owner' => 'SALES001'],
            [
                'nama' => 'Sales Test FIFO',
                'alamat' => 'Alamat Sales Test',
                'telepon' => '08123456789',
                'keterangan' => 'Sales untuk testing FIFO'
            ]
        );

        $customer = Customer::firstOrCreate(
            ['kode_customer' => 'CUST001'],
            [
                'nama' => 'Customer Test FIFO', 
                'alamat' => 'Alamat Test',
                'hp' => '08123456789',
                'email' => 'test@customer.com'
            ]
        );

        $transaksi = Transaksi::create([
            'no_transaksi' => 'TRX-TEST-' . time(),
            'tanggal' => now(),
            'kode_customer' => $customer->kode_customer,
            'sales' => $stokOwner->kode_stok_owner,
            'pembayaran' => 'Tunai',
            'cara_bayar' => 'Tunai',
            'subtotal' => $qtyDijual * 12000,
            'grand_total' => $qtyDijual * 12000,
            'status' => 'baru',
            'keterangan' => 'Test FIFO'
        ]);

        // Buat transaksi item
        $transaksiItem = TransaksiItem::create([
            'transaksi_id' => $transaksi->id,
            'no_transaksi' => $transaksi->no_transaksi,
            'kode_barang' => $kodeBarang->kode_barang,
            'nama_barang' => $kodeBarang->name,
            'harga' => 12000,
            'qty' => $qtyDijual,
            'total' => $qtyDijual * 12000
        ]);

        try {
            // Alokasi stok menggunakan FIFO
            $this->info("2. Melakukan alokasi FIFO...");
            $alokasiResult = $fifoService->alokasiStok($kodeBarang->id, $qtyDijual, $transaksiItem->id);
            
            $this->info("   ✅ Alokasi berhasil!");
            $this->info("   - Total harga modal: Rp " . number_format($alokasiResult['total_harga_modal'], 2));
            $this->info("   - Rata-rata harga modal: Rp " . number_format($alokasiResult['rata_rata_harga_modal'], 2));
            $this->info("   - Laba kotor: Rp " . number_format(($qtyDijual * 12000) - $alokasiResult['total_harga_modal'], 2));

            // Tampilkan detail alokasi
            $this->info("3. Detail alokasi batch:");
            foreach ($alokasiResult['alokasi'] as $alokasi) {
                $batch = StockBatch::find($alokasi['batch_id']);
                $this->line("   - Batch {$batch->batch_number}: {$alokasi['qty_ambil']} unit (Harga: Rp " . number_format($alokasi['harga_modal'], 2) . ")");
            }

            // Cek stok setelah penjualan
            $stokSesudah = $fifoService->getStokTersedia($kodeBarang->id);
            $this->info("4. Stok setelah penjualan: {$stokSesudah} unit");
            $this->info("   - Stok berkurang: " . ($stokSebelum - $stokSesudah) . " unit");

            // Cek transaksi item sumber
            $sumberCount = TransaksiItemSumber::where('transaksi_item_id', $transaksiItem->id)->count();
            $this->info("5. Record transaksi item sumber: {$sumberCount} record");

            $this->info("✅ Simulasi penjualan FIFO berhasil!");

        } catch (\Exception $e) {
            $this->error("❌ Error: " . $e->getMessage());
        }

        // Cleanup - hapus transaksi test
        $transaksi->delete();
    }
}
