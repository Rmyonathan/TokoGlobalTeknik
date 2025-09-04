<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\GrupBarang;
use Illuminate\Support\Facades\DB;

class GrupBarangSeeder extends Seeder
{
    public function run(): void
    {
        // Ambil semua attribute unik dari tabel kode_barangs
        $kodeBarangAttributes = DB::table('kode_barangs')
            ->select('attribute')
            ->whereNotNull('attribute')
            ->where('attribute', '!=', '')
            ->distinct()
            ->pluck('attribute')
            ->toArray();

        // Jika tidak ada data kode barang, gunakan data default
        if (empty($kodeBarangAttributes)) {
            $kodeBarangAttributes = [
                'Plastik Lembaran',
                'Plastik Gulungan', 
                'Plastik Kemasan',
                'Plastik Industri',
                'Plastik Test'
            ];
        }

        // Buat grup barang berdasarkan attribute kode barang
        foreach ($kodeBarangAttributes as $attribute) {
            GrupBarang::firstOrCreate(
                ['name' => $attribute],
                [
                    'name' => $attribute,
                    'description' => 'Grup barang berdasarkan attribute kode barang: ' . $attribute,
                    'status' => 'Active'
                ]
            );
        }

        $this->command->info('✅ Data grup barang berhasil dibuat berdasarkan attribute kode barang!');
        $this->command->info('Total grup barang: ' . count($kodeBarangAttributes));
    }
}
