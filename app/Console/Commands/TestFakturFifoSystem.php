<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Transaksi;
use App\Models\TransaksiItem;
use App\Models\TransaksiItemSumber;
use App\Models\SuratJalan;
use App\Models\SuratJalanItem;
use App\Models\SuratJalanItemSumber;
use App\Models\Customer;
use App\Models\KodeBarang;
use App\Models\StockBatch;
use App\Models\CustomerItemOngkos;
use App\Services\FifoService;
use App\Services\UnitConversionService;
use App\Http\Controllers\TransaksiController;
use Illuminate\Http\Request;

class TestFakturFifoSystem extends Command
{
    protected $signature = 'app:test-faktur-fifo-system';
    protected $description = 'Test sistem Faktur dengan FIFO allocation dan ongkos kuli dinamis';

    public function handle()
    {
        $this->info('🧪 Testing Sistem Faktur dengan FIFO...');
        $fifoService = new FifoService();
        $unitService = new UnitConversionService();

        // 1. Setup test data
        $this->info('1. Setup test data...');
        
        // Pastikan ada stok dengan menjalankan seeder
        $this->call('db:seed', ['--class' => 'FifoTestSeeder']);
        
        // Ambil customer
        $customer = Customer::where('kode_customer', 'CUST001')->first();
        if (!$customer) {
            $this->error('Customer CUST001 tidak ditemukan!');
            return;
        }

        // Ambil kode barang
        $kodeBarang = KodeBarang::where('kode_barang', 'KB001')->first();
        if (!$kodeBarang) {
            $this->error('Kode barang KB001 tidak ditemukan!');
            return;
        }

        $this->info('   ✅ Test data berhasil disiapkan');

        // 2. Test getHargaDanOngkos function
        $this->info('2. Testing getHargaDanOngkos function...');
        
        // Setup customer price khusus
        $customerPrice = \App\Models\CustomerPrice::firstOrCreate([
            'customer_id' => $customer->id,
            'kode_barang_id' => $kodeBarang->id,
        ], [
            'harga_jual_khusus' => 15000,
            'ongkos_kuli_khusus' => 2000,
            'unit_jual' => 'LBR',
            'is_active' => true,
            'keterangan' => 'Harga khusus untuk testing'
        ]);

        // Setup ongkos kuli khusus
        $ongkosKhusus = CustomerItemOngkos::updateOrCreateOngkos(
            $customer->id,
            $kodeBarang->id,
            3000,
            'Ongkos kuli testing'
        );

        // Test AJAX function
        $controller = new TransaksiController(
            app(\App\Http\Controllers\StockController::class),
            app(\App\Http\Controllers\PanelController::class)
        );

        $request = new Request([
            'customer_id' => $customer->id,
            'kode_barang_id' => $kodeBarang->id,
            'satuan' => 'LBR'
        ]);

        $response = $controller->getHargaDanOngkos($request);
        $responseData = json_decode($response->getContent(), true);

        $this->line("   - Response getHargaDanOngkos:");
        $this->line("     * Success: " . ($responseData['success'] ? 'Ya' : 'Tidak'));
        $this->line("     * Harga Jual: Rp " . number_format($responseData['harga_jual'], 0, ',', '.'));
        $this->line("     * Ongkos Kuli: Rp " . number_format($responseData['ongkos_kuli'], 0, ',', '.'));
        $this->line("     * Satuan: " . $responseData['satuan']);

        // 3. Test create Surat Jalan dengan FIFO (simulasi)
        $this->info('3. Testing create Surat Jalan dengan FIFO (simulasi)...');
        
        $stokSebelum = $fifoService->getStokTersedia($kodeBarang->id);
        $this->line("   - Stok tersedia: {$stokSebelum} LBR");

        // Buat transaksi dummy untuk SJ
        $transaksi = Transaksi::create([
            'no_transaksi' => 'TRX-TEST-FAKTUR-' . time(),
            'tanggal' => now(),
            'kode_customer' => $customer->kode_customer,
            'sales' => 'SALES001',
            'cara_bayar' => 'Tunai',
            'subtotal' => 150000,
            'grand_total' => 150000,
            'keterangan' => 'Test untuk Faktur FIFO'
        ]);

        $transaksiItem = TransaksiItem::create([
            'transaksi_id' => $transaksi->id,
            'no_transaksi' => $transaksi->no_transaksi,
            'kode_barang' => $kodeBarang->kode_barang,
            'nama_barang' => $kodeBarang->name,
            'qty' => 10,
            'satuan' => 'LBR',
            'harga' => 15000,
            'total' => 150000,
            'ongkos_kuli' => 3000
        ]);

        // Buat Surat Jalan
        $suratJalan = SuratJalan::create([
            'no_suratjalan' => 'SJ-TEST-FAKTUR-' . time(),
            'tanggal' => now(),
            'kode_customer' => $customer->kode_customer,
            'alamat_suratjalan' => 'Jl. Test Faktur No. 123',
            'no_transaksi' => $transaksi->no_transaksi,
            'tanggal_transaksi' => $transaksi->tanggal,
            'titipan_uang' => 0,
            'sisa_piutang' => 0
        ]);

        $suratJalanItem = SuratJalanItem::create([
            'no_suratjalan' => $suratJalan->no_suratjalan,
            'transaksi_id' => $transaksiItem->id,
            'kode_barang' => $kodeBarang->kode_barang,
            'nama_barang' => $kodeBarang->name,
            'qty' => 10
        ]);

        // FIFO allocation untuk Surat Jalan
        $qtyInBaseUnit = $unitService->convertToBaseUnit($kodeBarang->id, 10, 'LBR');
        $alokasiResult = $fifoService->alokasiStokUntukSuratJalan($kodeBarang->id, $qtyInBaseUnit, $suratJalanItem->id);
        
        // Catat alokasi di SuratJalanItemSumber
        foreach ($alokasiResult['alokasi'] as $alokasi) {
            SuratJalanItemSumber::create([
                'surat_jalan_item_id' => $suratJalanItem->id,
                'stock_batch_id' => $alokasi['batch_id'],
                'qty_diambil' => $alokasi['qty_ambil'],
                'harga_modal' => $alokasi['harga_modal']
            ]);
        }

        $this->line("   - Surat Jalan berhasil dibuat: {$suratJalan->no_suratjalan}");
        $this->line("   - FIFO allocation berhasil dengan rata-rata harga modal: Rp " . number_format($alokasiResult['rata_rata_harga_modal'], 0, ',', '.'));

        // 4. Test create Faktur dari Surat Jalan
        $this->info('4. Testing create Faktur dari Surat Jalan...');
        
        $faktur = Transaksi::create([
            'no_transaksi' => 'FAKTUR-TEST-' . time(),
            'tanggal' => now(),
            'kode_customer' => $customer->kode_customer,
            'sales' => 'SALES001',
            'cara_bayar' => 'Tunai',
            'subtotal' => 150000,
            'grand_total' => 150000,
            'keterangan' => 'Faktur dari Surat Jalan ' . $suratJalan->no_suratjalan
        ]);

        $fakturItem = TransaksiItem::create([
            'transaksi_id' => $faktur->id,
            'no_transaksi' => $faktur->no_transaksi,
            'kode_barang' => $kodeBarang->kode_barang,
            'nama_barang' => $kodeBarang->name,
            'qty' => 10,
            'satuan' => 'LBR',
            'harga' => 15000,
            'total' => 150000,
            'ongkos_kuli' => 3000
        ]);

        // Transfer FIFO allocation dari Surat Jalan ke Faktur
        $suratJalanItemSumber = SuratJalanItemSumber::where('surat_jalan_item_id', $suratJalanItem->id)->get();
        
        foreach ($suratJalanItemSumber as $sumber) {
            TransaksiItemSumber::create([
                'transaksi_item_id' => $fakturItem->id,
                'stock_batch_id' => $sumber->stock_batch_id,
                'qty_diambil' => $sumber->qty_diambil,
                'harga_modal' => $sumber->harga_modal
            ]);
        }

        $this->line("   - Faktur berhasil dibuat: {$faktur->no_transaksi}");
        $this->line("   - FIFO allocation berhasil ditransfer dari Surat Jalan");

        // 5. Test analisis FIFO allocation
        $this->info('5. Testing analisis FIFO allocation...');
        
        $fakturItem->load(['transaksiItemSumber.stockBatch.pembelianItem.pembelian.supplierRelation']);
        
        $totalHargaModal = 0;
        $totalQty = 0;
        
        foreach ($fakturItem->transaksiItemSumber as $sumber) {
            $this->line("   - Detail FIFO allocation:");
            $this->line("     * Batch: {$sumber->stockBatch->batch_number}");
            $this->line("     * Qty: {$sumber->qty_diambil} LBR");
            $this->line("     * Harga Modal: Rp " . number_format($sumber->harga_modal, 0, ',', '.'));
            $this->line("     * Supplier: " . ($sumber->stockBatch->pembelianItem->pembelian->supplierRelation->nama ?? 'Unknown'));
            
            $totalHargaModal += ($sumber->qty_diambil * $sumber->harga_modal);
            $totalQty += $sumber->qty_diambil;
        }

        $rataRataHargaModal = $totalQty > 0 ? $totalHargaModal / $totalQty : 0;
        $totalPenjualan = $fakturItem->qty * $fakturItem->harga;
        $grossProfit = $totalPenjualan - $totalHargaModal;
        $totalOngkosKuli = $fakturItem->ongkos_kuli;
        $netProfit = $grossProfit - $totalOngkosKuli;

        $this->line("   - Analisis Profitabilitas:");
        $this->line("     * Total Penjualan: Rp " . number_format($totalPenjualan, 0, ',', '.'));
        $this->line("     * Total Harga Modal (FIFO): Rp " . number_format($totalHargaModal, 0, ',', '.'));
        $this->line("     * Gross Profit: Rp " . number_format($grossProfit, 0, ',', '.'));
        $this->line("     * Ongkos Kuli: Rp " . number_format($totalOngkosKuli, 0, ',', '.'));
        $this->line("     * Net Profit: Rp " . number_format($netProfit, 0, ',', '.'));
        $this->line("     * Margin (%): " . number_format(($netProfit / $totalPenjualan) * 100, 2) . "%");

        // 6. Test update ongkos kuli customer
        $this->info('6. Testing update ongkos kuli customer...');
        
        $ongkosLama = CustomerItemOngkos::getOngkosKuli($customer->id, $kodeBarang->id);
        $this->line("   - Ongkos kuli lama: Rp " . number_format($ongkosLama, 0, ',', '.'));

        // Update ongkos kuli
        CustomerItemOngkos::updateOrCreateOngkos(
            $customer->id,
            $kodeBarang->id,
            3500,
            'Update dari faktur ' . $faktur->no_transaksi
        );

        $ongkosBaru = CustomerItemOngkos::getOngkosKuli($customer->id, $kodeBarang->id);
        $this->line("   - Ongkos kuli baru: Rp " . number_format($ongkosBaru, 0, ',', '.'));

        // 7. Test stok setelah transaksi
        $this->info('7. Testing stok setelah transaksi...');
        
        $stokSesudah = $fifoService->getStokTersedia($kodeBarang->id);
        $this->line("   - Stok tersedia setelah: {$stokSesudah} LBR");
        $this->line("   - Pengurangan stok: " . ($stokSebelum - $stokSesudah) . " LBR");

        // 8. Test batch status
        $this->info('8. Testing batch status...');
        
        $batches = StockBatch::where('kode_barang_id', $kodeBarang->id)->orderBy('tanggal_masuk', 'asc')->get();
        
        foreach ($batches as $batch) {
            $this->line("   - Batch {$batch->batch_number}:");
            $this->line("     * Qty masuk: {$batch->qty_masuk} LBR");
            $this->line("     * Qty sisa: {$batch->qty_sisa} LBR");
            $this->line("     * Harga beli: Rp " . number_format($batch->harga_beli, 0, ',', '.'));
            $this->line("     * Status: " . ($batch->qty_sisa > 0 ? 'Masih ada stok' : 'Habis'));
        }

        // Cleanup (dalam urutan yang benar untuk menghindari foreign key constraint)
        $suratJalanItem->delete();
        $suratJalan->delete();
        $fakturItem->delete();
        $faktur->delete();
        $transaksiItem->delete();
        $transaksi->delete();
        $ongkosKhusus->delete();
        $customerPrice->delete();

        $this->info('✅ Testing sistem Faktur dengan FIFO selesai!');
    }
}