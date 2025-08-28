<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\KategoriBarang;

class KategoriBarangSeeder extends Seeder
{
    public function run(): void
    {
        $kategoris = [
            [
                'name' => 'Plastik Lembaran',
                'description' => 'Plastik dalam bentuk lembaran (LBR)'
            ],
            [
                'name' => 'Plastik Gulungan',
                'description' => 'Plastik dalam bentuk gulungan (ROLL)'
            ],
            [
                'name' => 'Plastik Kemasan',
                'description' => 'Plastik untuk kemasan (PACK)'
            ],
            [
                'name' => 'Plastik Industri',
                'description' => 'Plastik untuk keperluan industri'
            ],
            [
                'name' => 'Plastik Test',
                'description' => 'Kategori untuk testing unit conversion'
            ]
        ];

        foreach ($kategoris as $kategori) {
            KategoriBarang::firstOrCreate(
                ['name' => $kategori['name']],
                $kategori
            );
        }

        $this->command->info('✅ Data kategori barang berhasil dibuat!');
    }
}
