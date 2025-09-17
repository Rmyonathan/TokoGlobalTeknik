<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\KodeBarang;
use App\Models\GrupBarang;
use App\Models\Stock;
use App\Models\StokOwner;

class TestDecimalQuantitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Buat grup barang untuk testing
        $grupBarang = GrupBarang::firstOrCreate([
            'name' => 'Material Konstruksi'
        ], [
            'description' => 'Barang-barang untuk testing decimal quantity',
            'status' => 'Active'
        ]);

        // Data barang yang mendukung quantity decimal
        $barangData = [
            [
                'kode_barang' => '001',
                'name' => 'Kursi',
                'merek' => 'ABS',
                'ukuran' => 'Inch/Meter',
                'unit_dasar' => 'PCS',
                'satuan_dasar' => 'PCS',
                'satuan_besar' => 'PAKET',
                'nilai_konversi' => 12,
                'harga_jual' => 0.00,
                'cost' => 0.00,
                'grup_barang_id' => $grupBarang->id,
                'attribute' => 'Material Konstruksi',
                'status' => 'Active',
                'ongkos_kuli_default' => 0
            ]
            // [
            //     'kode_barang' => 'KBL-001',
            //     'name' => 'Kabel Listrik 2.5mm',
            //     'unit_dasar' => 'MTR',
            //     'harga_jual' => 15000.00,
            //     'cost' => 12000.00,
            //     'grup_barang_id' => $grupBarang->id,
            //     'attribute' => 'Material Konstruksi',
            //     'status' => 'Active',
            //     'ongkos_kuli_default' => 0
            // ],
            // [
            //     'kode_barang' => 'KBL-002',
            //     'name' => 'Kabel Listrik 4mm',
            //     'unit_dasar' => 'MTR',
            //     'harga_jual' => 25000.00,
            //     'cost' => 20000.00,
            //     'grup_barang_id' => $grupBarang->id,
            //     'attribute' => 'Material Konstruksi',
            //     'status' => 'Active',
            //     'ongkos_kuli_default' => 0
            // ],
            // [
            //     'kode_barang' => 'KIN-001',
            //     'name' => 'Kain Katun 100%',
            //     'unit_dasar' => 'YRD',
            //     'harga_jual' => 35000.00,
            //     'cost' => 28000.00,
            //     'grup_barang_id' => $grupBarang->id,
            //     'attribute' => 'Material Konstruksi',
            //     'status' => 'Active',
            //     'ongkos_kuli_default' => 0
            // ],
            // [
            //     'kode_barang' => 'KIN-002',
            //     'name' => 'Kain Polyester',
            //     'unit_dasar' => 'YRD',
            //     'harga_jual' => 28000.00,
            //     'cost' => 22000.00,
            //     'grup_barang_id' => $grupBarang->id,
            //     'attribute' => 'Material Konstruksi',
            //     'status' => 'Active',
            //     'ongkos_kuli_default' => 0
            // ],
            // [
            //     'kode_barang' => 'PIP-001',
            //     'name' => 'Pipa PVC 1/2 inch',
            //     'unit_dasar' => 'MTR',
            //     'harga_jual' => 8000.00,
            //     'cost' => 6000.00,
            //     'grup_barang_id' => $grupBarang->id,
            //     'attribute' => 'Material Konstruksi',
            //     'status' => 'Active',
            //     'ongkos_kuli_default' => 0
            // ],
            // [
            //     'kode_barang' => 'PIP-002',
            //     'name' => 'Pipa PVC 3/4 inch',
            //     'unit_dasar' => 'MTR',
            //     'harga_jual' => 12000.00,
            //     'cost' => 9500.00,
            //     'grup_barang_id' => $grupBarang->id,
            //     'attribute' => 'Material Konstruksi',
            //     'status' => 'Active',
            //     'ongkos_kuli_default' => 0
            // ],
            // [
            //     'kode_barang' => 'BES-001',
            //     'name' => 'Besi Beton 8mm',
            //     'unit_dasar' => 'MTR',
            //     'harga_jual' => 18000.00,
            //     'cost' => 15000.00,
            //     'grup_barang_id' => $grupBarang->id,
            //     'attribute' => 'Material Konstruksi',
            //     'status' => 'Active',
            //     'ongkos_kuli_default' => 0
            // ],
            // [
            //     'kode_barang' => 'BES-002',
            //     'name' => 'Besi Beton 10mm',
            //     'unit_dasar' => 'MTR',
            //     'harga_jual' => 25000.00,
            //     'cost' => 20000.00,
            //     'grup_barang_id' => $grupBarang->id,
            //     'attribute' => 'Material Konstruksi',
            //     'status' => 'Active',
            //     'ongkos_kuli_default' => 0
            // ],
            // [
            //     'kode_barang' => 'KAT-001',
            //     'name' => 'Kawat Galvanis 2mm',
            //     'unit_dasar' => 'MTR',
            //     'harga_jual' => 5000.00,
            //     'cost' => 4000.00,
            //     'grup_barang_id' => $grupBarang->id,
            //     'attribute' => 'Material Konstruksi',
            //     'status' => 'Active',
            //     'ongkos_kuli_default' => 0
            // ],
            // [
            //     'kode_barang' => 'KAT-002',
            //     'name' => 'Kawat Galvanis 3mm',
            //     'unit_dasar' => 'MTR',
            //     'harga_jual' => 7500.00,
            //     'cost' => 6000.00,
            //     'grup_barang_id' => $grupBarang->id,
            //     'attribute' => 'Material Konstruksi',
            //     'status' => 'Active',
            //     'ongkos_kuli_default' => 0
            // ],
            // [
            //     'kode_barang' => 'PLT-001',
            //     'name' => 'Plat Besi 2mm',
            //     'unit_dasar' => 'M2',
            //     'harga_jual' => 45000.00,
            //     'cost' => 38000.00,
            //     'grup_barang_id' => $grupBarang->id,
            //     'attribute' => 'Material Konstruksi',
            //     'status' => 'Active',
            //     'ongkos_kuli_default' => 0
            // ],
            // [
            //     'kode_barang' => 'PLT-002',
            //     'name' => 'Plat Besi 3mm',
            //     'unit_dasar' => 'M2',
            //     'harga_jual' => 65000.00,
            //     'cost' => 55000.00,
            //     'grup_barang_id' => $grupBarang->id,
            //     'attribute' => 'Material Konstruksi',
            //     'status' => 'Active',
            //     'ongkos_kuli_default' => 0
            // ]
        ];

        // Insert barang
        foreach ($barangData as $barang) {
            $kodeBarang = KodeBarang::updateOrCreate(
                ['kode_barang' => $barang['kode_barang']],
                $barang
            );

            // Buat stok untuk setiap barang
            Stock::updateOrCreate([
                'kode_barang' => $kodeBarang->kode_barang
            ], [
                'nama_barang' => $kodeBarang->name,
                'good_stock' => 100.50, // Stok dengan decimal
                'bad_stock' => 5.25,    // Stok rusak dengan decimal
                'satuan' => $kodeBarang->unit_dasar
            ]);

            echo "Created barang: {$kodeBarang->kode_barang} - {$kodeBarang->name}\n";
        }

        echo "\n=== Test Data Created Successfully ===\n";
        echo "Total barang created: " . count($barangData) . "\n";
        echo "All items support decimal quantity (MTR, YRD, M2 units)\n";
        echo "Sample quantities for testing:\n";
        echo "- 0.5 meter kabel = Rp " . number_format(15000 * 0.5, 0, ',', '.') . "\n";
        echo "- 1.25 yard kain = Rp " . number_format(35000 * 1.25, 0, ',', '.') . "\n";
        echo "- 2.75 meter pipa = Rp " . number_format(8000 * 2.75, 0, ',', '.') . "\n";
    }
}
