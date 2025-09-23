<?php

namespace Database\Seeders;

use App\Models\KodeBarang;
use App\Models\GrupBarang;
use App\Models\Stock;
use App\Models\StockBatch;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BarangSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clean existing data
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        try { DB::table('stock_batches')->truncate(); } catch (\Throwable $e) {}
        try { DB::table('stocks')->truncate(); } catch (\Throwable $e) {}
        try { DB::table('kode_barangs')->truncate(); } catch (\Throwable $e) {}
        try { DB::table('grup_barangs')->truncate(); } catch (\Throwable $e) {}
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        // Create Grup Barang
        $grupBarangs = [
            ['name' => 'Plastik Lembaran', 'description' => 'Plastik dalam bentuk lembaran', 'status' => 'Active'],
            ['name' => 'Plastik Gulungan', 'description' => 'Plastik dalam bentuk gulungan', 'status' => 'Active'],
            ['name' => 'Plastik Kemasan', 'description' => 'Plastik untuk kemasan', 'status' => 'Active'],
            ['name' => 'Plastik Industri', 'description' => 'Plastik untuk industri', 'status' => 'Active'],
            ['name' => 'Aksesoris', 'description' => 'Aksesoris dan spare part', 'status' => 'Active'],
        ];

        $grupIds = [];
        foreach ($grupBarangs as $grup) {
            $grupBarang = GrupBarang::create($grup);
            $grupIds[] = $grupBarang->id;
        }

        // Create Kode Barang
        $barangs = [
            // Plastik Lembaran
            ['kode_barang' => 'PL001', 'name' => 'Plastik LDPE 0.1mm', 'attribute' => 'Plastik Lembaran', 'unit_dasar' => 'LBR', 'cost' => 15000, 'harga_jual' => 18000],
            ['kode_barang' => 'PL002', 'name' => 'Plastik HDPE 0.2mm', 'attribute' => 'Plastik Lembaran', 'unit_dasar' => 'LBR', 'cost' => 25000, 'harga_jual' => 30000],
            ['kode_barang' => 'PL003', 'name' => 'Plastik PP 0.15mm', 'attribute' => 'Plastik Lembaran', 'unit_dasar' => 'LBR', 'cost' => 20000, 'harga_jual' => 24000],
            
            // Plastik Gulungan
            ['kode_barang' => 'PG001', 'name' => 'Plastik Roll LDPE 0.1mm', 'attribute' => 'Plastik Gulungan', 'unit_dasar' => 'ROLL', 'cost' => 150000, 'harga_jual' => 180000],
            ['kode_barang' => 'PG002', 'name' => 'Plastik Roll HDPE 0.2mm', 'attribute' => 'Plastik Gulungan', 'unit_dasar' => 'ROLL', 'cost' => 250000, 'harga_jual' => 300000],
            
            // Plastik Kemasan
            ['kode_barang' => 'PK001', 'name' => 'Kantong Plastik Kecil', 'attribute' => 'Plastik Kemasan', 'unit_dasar' => 'PCS', 'cost' => 500, 'harga_jual' => 750],
            ['kode_barang' => 'PK002', 'name' => 'Kantong Plastik Besar', 'attribute' => 'Plastik Kemasan', 'unit_dasar' => 'PCS', 'cost' => 1000, 'harga_jual' => 1500],
            
            // Plastik Industri
            ['kode_barang' => 'PI001', 'name' => 'Plastik Industri HDPE 1mm', 'attribute' => 'Plastik Industri', 'unit_dasar' => 'LBR', 'cost' => 50000, 'harga_jual' => 60000],
            ['kode_barang' => 'PI002', 'name' => 'Plastik Industri PP 2mm', 'attribute' => 'Plastik Industri', 'unit_dasar' => 'LBR', 'cost' => 75000, 'harga_jual' => 90000],
            
            // Aksesoris
            ['kode_barang' => 'AK001', 'name' => 'Tali Plastik', 'attribute' => 'Aksesoris', 'unit_dasar' => 'METER', 'cost' => 2000, 'harga_jual' => 3000],
            ['kode_barang' => 'AK002', 'name' => 'Klem Plastik', 'attribute' => 'Aksesoris', 'unit_dasar' => 'PCS', 'cost' => 100, 'harga_jual' => 150],
        ];

        $barangIds = [];
        foreach ($barangs as $barang) {
            $grupId = $grupIds[array_rand($grupIds)]; // Random group assignment
            $kodeBarang = KodeBarang::create([
                'kode_barang' => $barang['kode_barang'],
                'name' => $barang['name'],
                'attribute' => $barang['attribute'],
                'unit_dasar' => $barang['unit_dasar'],
                'cost' => $barang['cost'],
                'harga_jual' => $barang['harga_jual'],
                'grup_barang_id' => $grupId,
                'status' => 'Active',
            ]);
            $barangIds[] = $kodeBarang->id;
        }

        // Create Stock records
        foreach ($barangs as $index => $barang) {
            Stock::create([
                'kode_barang' => $barang['kode_barang'],
                'nama_barang' => $barang['name'],
                'good_stock' => rand(50, 500),
                'bad_stock' => rand(0, 10),
                'so' => 0,
                'satuan' => $barang['unit_dasar'],
            ]);
        }

        // Create Stock Batches (FIFO data)
        foreach ($barangs as $index => $barang) {
            $kodeBarangId = $barangIds[$index];
            $totalStock = rand(50, 500);
            $remainingStock = $totalStock;
            
            // Create 2-4 batches per item
            $batchCount = rand(2, 4);
            for ($i = 0; $i < $batchCount; $i++) {
                $batchQty = $i === $batchCount - 1 ? $remainingStock : rand(10, $remainingStock - 10);
                $remainingStock -= $batchQty;
                
                // Vary purchase price slightly for FIFO testing
                $priceVariation = rand(-5, 5) / 100; // ±5% variation
                $hargaBeli = $barang['cost'] * (1 + $priceVariation);
                
                StockBatch::create([
                    'kode_barang_id' => $kodeBarangId,
                    'qty_masuk' => $batchQty,
                    'qty_sisa' => $batchQty,
                    'harga_beli' => round($hargaBeli, 2),
                    'tanggal_masuk' => now()->subDays(rand(1, 30)),
                    'batch_number' => 'BATCH-' . $barang['kode_barang'] . '-' . ($i + 1),
                    'keterangan' => 'Supplier ' . chr(65 + $i), // A, B, C, D
                ]);
            }
        }

        $this->command->info('✅ Data barang berhasil dibuat!');
        $this->command->info('Total grup barang: ' . count($grupBarangs));
        $this->command->info('Total kode barang: ' . count($barangs));
        $this->command->info('Total stock records: ' . count($barangs));
        $this->command->info('Total stock batches: ' . (count($barangs) * 3)); // Average 3 batches per item
    }
}
