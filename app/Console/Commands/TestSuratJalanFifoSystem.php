<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SuratJalan;
use App\Models\SuratJalanItem;
use App\Models\SuratJalanItemSumber;
use App\Models\Customer;
use App\Models\Transaksi;
use App\Models\TransaksiItem;
use App\Models\KodeBarang;
use App\Models\StockBatch;
use App\Services\FifoService;
use App\Services\UnitConversionService;

class TestSuratJalanFifoSystem extends Command
{
    protected $signature = 'app:test-surat-jalan-fifo-system';
    protected $description = 'Test sistem Surat Jalan dengan FIFO allocation';

    public function handle()
    {
        $this->info('🧪 Testing Sistem Surat Jalan dengan FIFO...');
        $fifoService = new FifoService();
        $unitService = new UnitConversionService();

        // 1. Setup test data
        $this->info('1. Setup test data...');
        
        // Ambil customer
        $customer = Customer::where('kode_customer', 'CUST001')->first();
        if (!$customer) {
            $this->error('Customer CUST001 tidak ditemukan! Jalankan TestCustomerCreditSystem terlebih dahulu.');
            return;
        }

        // Ambil kode barang
        $kodeBarang = KodeBarang::where('kode_barang', 'KB001')->first();
        if (!$kodeBarang) {
            $this->error('Kode barang KB001 tidak ditemukan! Jalankan FifoTestSeeder terlebih dahulu.');
            return;
        }

        // Buat transaksi dummy untuk testing
        $transaksi = Transaksi::create([
            'no_transaksi' => 'TRX-TEST-SJ-' . time(),
            'tanggal' => now(),
            'kode_customer' => $customer->kode_customer,
            'sales' => 'SALES001',
            'cara_bayar' => 'Tunai',
            'subtotal' => 1000000,
            'grand_total' => 1000000,
            'keterangan' => 'Test untuk Surat Jalan FIFO'
        ]);

        $transaksiItem = TransaksiItem::create([
            'transaksi_id' => $transaksi->id,
            'no_transaksi' => $transaksi->no_transaksi,
            'kode_barang' => $kodeBarang->kode_barang,
            'nama_barang' => $kodeBarang->name,
            'name' => $kodeBarang->name,
            'qty' => 50,
            'harga' => 20000,
            'total' => 1000000
        ]);

        $this->info('   ✅ Test data berhasil dibuat');

        // 2. Test stok tersedia sebelum Surat Jalan
        $this->info('2. Testing stok tersedia sebelum Surat Jalan...');
        
        $stokSebelum = $fifoService->getStokTersedia($kodeBarang->id);
        $this->line("   - Stok tersedia sebelum SJ: {$stokSebelum} LBR");

        // 3. Test create Surat Jalan dengan FIFO
        $this->info('3. Testing create Surat Jalan dengan FIFO...');
        
        $suratJalan = SuratJalan::create([
            'no_suratjalan' => 'SJ-TEST-' . time(),
            'tanggal' => now(),
            'kode_customer' => $customer->kode_customer,
            'alamat_suratjalan' => 'Jl. Test No. 123',
            'no_transaksi' => $transaksi->no_transaksi,
            'tanggal_transaksi' => $transaksi->tanggal,
            'titipan_uang' => 0,
            'sisa_piutang' => 0
        ]);

        $this->line("   - Surat Jalan berhasil dibuat: {$suratJalan->no_suratjalan}");

        // 4. Test create Surat Jalan Item dengan FIFO allocation
        $this->info('4. Testing create Surat Jalan Item dengan FIFO allocation...');
        
        $qtyKirim = 2; // Kirim 2 LBR (sesuai stok tersedia)
        $qtyInBaseUnit = $unitService->convertToBaseUnit($kodeBarang->id, $qtyKirim, 'LBR');

        $suratJalanItem = SuratJalanItem::create([
            'no_suratjalan' => $suratJalan->no_suratjalan,
            'transaksi_id' => $transaksiItem->id,
            'kode_barang' => $kodeBarang->kode_barang,
            'nama_barang' => $kodeBarang->name,
            'qty' => $qtyKirim
        ]);

        $this->line("   - Surat Jalan Item berhasil dibuat: {$qtyKirim} LBR");

        // 5. Test FIFO allocation
        $this->info('5. Testing FIFO allocation...');
        
        $alokasiResult = $fifoService->alokasiStokUntukSuratJalan($kodeBarang->id, $qtyInBaseUnit, $suratJalanItem->id);
        
        $this->line("   - FIFO allocation berhasil:");
        $this->line("     * Total harga modal: Rp " . number_format($alokasiResult['total_harga_modal'], 0, ',', '.'));
        $this->line("     * Rata-rata harga modal: Rp " . number_format($alokasiResult['rata_rata_harga_modal'], 0, ',', '.'));

        // 6. Test create SuratJalanItemSumber records
        $this->info('6. Testing create SuratJalanItemSumber records...');
        
        foreach ($alokasiResult['alokasi'] as $alokasi) {
            SuratJalanItemSumber::create([
                'surat_jalan_item_id' => $suratJalanItem->id,
                'stock_batch_id' => $alokasi['batch_id'],
                'qty_diambil' => $alokasi['qty_ambil'],
                'harga_modal' => $alokasi['harga_modal']
            ]);

            $this->line("     * Batch ID: {$alokasi['batch_id']}, Qty: {$alokasi['qty_ambil']}, Harga: Rp " . number_format($alokasi['harga_modal'], 0, ',', '.'));
        }

        // 7. Test stok tersedia setelah Surat Jalan
        $this->info('7. Testing stok tersedia setelah Surat Jalan...');
        
        $stokSesudah = $fifoService->getStokTersedia($kodeBarang->id);
        $this->line("   - Stok tersedia setelah SJ: {$stokSesudah} LBR");
        $this->line("   - Pengurangan stok: " . ($stokSebelum - $stokSesudah) . " LBR");

        // 8. Test detail alokasi FIFO
        $this->info('8. Testing detail alokasi FIFO...');
        
        $suratJalanItem->load(['suratJalanItemSumber.stockBatch.pembelianItem.pembelian.supplierRelation']);
        
        foreach ($suratJalanItem->suratJalanItemSumber as $sumber) {
            $this->line("   - Detail alokasi:");
            $this->line("     * Batch: {$sumber->stockBatch->batch_number}");
            $this->line("     * Qty diambil: {$sumber->qty_diambil} LBR");
            $this->line("     * Harga modal: Rp " . number_format($sumber->harga_modal, 0, ',', '.'));
            $this->line("     * Supplier: " . ($sumber->stockBatch->pembelianItem->pembelian->supplierRelation->nama ?? 'Unknown'));
            $this->line("     * Tanggal masuk: " . $sumber->stockBatch->tanggal_masuk->format('d/m/Y'));
        }

        // 9. Test batch status setelah alokasi
        $this->info('9. Testing batch status setelah alokasi...');
        
        $batches = StockBatch::where('kode_barang_id', $kodeBarang->id)->orderBy('tanggal_masuk', 'asc')->get();
        
        foreach ($batches as $batch) {
            $this->line("   - Batch {$batch->batch_number}:");
            $this->line("     * Qty masuk: {$batch->qty_masuk} LBR");
            $this->line("     * Qty sisa: {$batch->qty_sisa} LBR");
            $this->line("     * Status: " . ($batch->qty_sisa > 0 ? 'Masih ada stok' : 'Habis'));
        }

        // 10. Test unit conversion dalam konteks Surat Jalan
        $this->info('10. Testing unit conversion dalam konteks Surat Jalan...');
        
        $qtyDus = $unitService->convertFromBaseUnit($kodeBarang->id, 40, 'DUS');
        $this->line("   - 40 LBR = {$qtyDus} DUS");

        $qtyLbr = $unitService->convertToBaseUnit($kodeBarang->id, 2, 'DUS');
        $this->line("   - 2 DUS = {$qtyLbr} LBR");

        // Cleanup
        $suratJalanItem->delete();
        $suratJalan->delete();
        $transaksiItem->delete();
        $transaksi->delete();

        $this->info('✅ Testing sistem Surat Jalan dengan FIFO selesai!');
    }
}
