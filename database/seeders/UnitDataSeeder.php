<?php

namespace Database\Seeders;

use App\Models\KodeBarang;
use App\Models\UnitConversion;
use Illuminate\Database\Seeder;

class UnitDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Update existing kode barang with unit_dasar
        $kodeBarangs = KodeBarang::all();
        foreach ($kodeBarangs as $kb) {
            if (empty($kb->unit_dasar)) {
                $kb->update(['unit_dasar' => 'LBR']);
            }
        }

        // Create unit conversions for existing kode barang
        $kodeBarang = KodeBarang::first();
        if ($kodeBarang) {
            // Create unit conversions
            $unitConversions = [
                [
                    'kode_barang_id' => $kodeBarang->id,
                    'unit_turunan' => 'M2',
                    'nilai_konversi' => 2, // 1 M2 = 2 LBR (1 LBR = 0.5 M2)
                    'is_active' => true,
                ],
                [
                    'kode_barang_id' => $kodeBarang->id,
                    'unit_turunan' => 'KG',
                    'nilai_konversi' => 1, // 1 KG = 1 LBR (1 LBR = 1 KG)
                    'is_active' => true,
                ],
            ];

            foreach ($unitConversions as $conversion) {
                UnitConversion::updateOrCreate(
                    [
                        'kode_barang_id' => $conversion['kode_barang_id'],
                        'unit_turunan' => $conversion['unit_turunan'],
                    ],
                    $conversion
                );
            }
        }

        echo "✅ Unit data berhasil diupdate!\n";
        echo "Kode Barang dengan unit_dasar: " . KodeBarang::whereNotNull('unit_dasar')->count() . "\n";
        echo "Unit Conversions: " . UnitConversion::count() . "\n";
    }
}
