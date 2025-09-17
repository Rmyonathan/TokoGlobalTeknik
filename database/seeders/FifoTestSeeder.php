<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\KodeBarang;
use App\Models\StockBatch;
use App\Models\Supplier;
use App\Models\Pembelian;
use App\Models\PembelianItem;

class FifoTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Buat supplier test jika belum ada
        $supplier = Supplier::firstOrCreate(
            ['kode_supplier' => 'SUP001'],
            [
                'nama' => 'Supplier Test FIFO',
                'alamat' => 'Alamat Test',
                'telepon' => '08123456789',
                'email' => 'test@supplier.com'
            ]
        );

        // Buat kode barang test
        $kodeBarang = KodeBarang::firstOrCreate(
            ['kode_barang' => 'KB001'],
            [
                'name' => 'Plastik Test FIFO',
                'cost' => 8000,
                'harga_jual' => 12000,
                'attribute' => 'Test FIFO',
                'status' => 'Active'
            ]
        );

        // Buat pembelian test
        $pembelian = Pembelian::firstOrCreate(
            ['nota' => 'BL-TEST-001'],
            [
                'tanggal' => now(),
                'kode_supplier' => $supplier->kode_supplier,
                'pembayaran' => 'Tunai',
                'cara_bayar' => 'Tunai',
                'subtotal' => 1000000,
                'grand_total' => 1000000
            ]
        );

        // Buat pembelian item
        $pembelianItem = PembelianItem::firstOrCreate(
            ['nota' => $pembelian->nota, 'kode_barang' => $kodeBarang->kode_barang],
            [
                'nama_barang' => $kodeBarang->name,
                'harga' => 10000,
                'qty' => 100,
                'total' => 1000000
            ]
        );

        // Buat stock batch dengan harga berbeda untuk testing FIFO
        $batchData = [
            ['tanggal' => now()->subDays(30), 'harga' => 8000, 'qty' => 50],
            ['tanggal' => now()->subDays(20), 'harga' => 9000, 'qty' => 30],
            ['tanggal' => now()->subDays(10), 'harga' => 10000, 'qty' => 20],
        ];

        foreach ($batchData as $index => $data) {
            StockBatch::create([
                'kode_barang_id' => $kodeBarang->id,
                'pembelian_item_id' => $pembelianItem->id,
                'qty_masuk' => $data['qty'],
                'qty_sisa' => $data['qty'],
                'harga_beli' => $data['harga'],
                'tanggal_masuk' => $data['tanggal'],
                'batch_number' => "BATCH-" . ($index + 1),
                'keterangan' => 'Batch test FIFO'
            ]);
        }

        $this->command->info('✅ Data test FIFO berhasil dibuat!');
        $this->command->info("   - Supplier: {$supplier->nama}");
        $this->command->info("   - Kode Barang: {$kodeBarang->kode_barang}");
        $this->command->info("   - Total Stok: " . StockBatch::where('kode_barang_id', $kodeBarang->id)->sum('qty_sisa'));
    }
}
