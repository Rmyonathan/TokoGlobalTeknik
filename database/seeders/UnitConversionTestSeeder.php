<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\KodeBarang;
use App\Models\GrupBarang;
use App\Models\Stock;
use App\Models\UnitConversion;

class UnitConversionTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Buat grup barang untuk testing konversi
        $grupBarang = GrupBarang::firstOrCreate([
            'name' => 'Material dengan Konversi Satuan'
        ], [
            'description' => 'Barang-barang dengan konversi inch ke meter',
            'status' => 'Active'
        ]);

        // Data barang dengan konversi inch ke meter
        $barangData = [
            [
                'kode_barang' => 'PIP-INCH-001',
                'name' => 'Pipa PVC 1/2 inch (per inch)',
                'unit_dasar' => 'INCH',
                'harga_jual' => 203.20, // Rp 8,000 per meter = Rp 203.20 per inch
                'cost' => 152.40,       // Rp 6,000 per meter = Rp 152.40 per inch
                'grup_barang_id' => $grupBarang->id,
                'attribute' => 'Material Konstruksi',
                'status' => 'Active',
                'ongkos_kuli_default' => 0
            ],
            [
                'kode_barang' => 'KBL-INCH-001',
                'name' => 'Kabel Listrik 2.5mm (per inch)',
                'unit_dasar' => 'INCH',
                'harga_jual' => 380.95, // Rp 15,000 per meter = Rp 380.95 per inch
                'cost' => 304.76,       // Rp 12,000 per meter = Rp 304.76 per inch
                'grup_barang_id' => $grupBarang->id,
                'attribute' => 'Material Konstruksi',
                'status' => 'Active',
                'ongkos_kuli_default' => 0
            ],
            [
                'kode_barang' => 'BES-INCH-001',
                'name' => 'Besi Beton 8mm (per inch)',
                'unit_dasar' => 'INCH',
                'harga_jual' => 457.14, // Rp 18,000 per meter = Rp 457.14 per inch
                'cost' => 380.95,       // Rp 15,000 per meter = Rp 380.95 per inch
                'grup_barang_id' => $grupBarang->id,
                'attribute' => 'Material Konstruksi',
                'status' => 'Active',
                'ongkos_kuli_default' => 0
            ]
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
                'good_stock' => 1000.00, // Stok dalam inch
                'bad_stock' => 50.00,    // Stok rusak dalam inch
                'satuan' => $kodeBarang->unit_dasar
            ]);

            // Buat konversi satuan: 1 meter = 39.37 inch
            UnitConversion::updateOrCreate([
                'kode_barang_id' => $kodeBarang->id,
                'unit_turunan' => 'MTR'
            ], [
                'nilai_konversi' => 39.37, // 1 meter = 39.37 inch
                'is_active' => true
            ]);

            echo "Created barang: {$kodeBarang->kode_barang} - {$kodeBarang->name}\n";
        }

        echo "\n=== Unit Conversion Test Data Created Successfully ===\n";
        echo "Total barang created: " . count($barangData) . "\n";
        echo "All items support inch to meter conversion\n";
        echo "Conversion rate: 1 meter = 39.37 inch\n";
        echo "\nSample calculations:\n";
        echo "- 24 inch pipa = 24 ÷ 39.37 = 0.609 meter\n";
        echo "- 36 inch kabel = 36 ÷ 39.37 = 0.914 meter\n";
        echo "- 48 inch besi = 48 ÷ 39.37 = 1.219 meter\n";
    }
}
