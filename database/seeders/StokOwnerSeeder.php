<?php

namespace Database\Seeders;

use App\Models\StokOwner;
use Illuminate\Database\Seeder;

class StokOwnerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $stokOwners = [
            [
                'kode_stok_owner' => 'SO001',
                'keterangan' => 'Salesman 1',
                'default' => 1,
            ],
            [
                'kode_stok_owner' => 'SO002',
                'keterangan' => 'Salesman 2',
                'default' => 0,
            ],
            [
                'kode_stok_owner' => 'SO003',
                'keterangan' => 'Salesman 3',
                'default' => 0,
            ],
            [
                'kode_stok_owner' => 'SO004',
                'keterangan' => 'Salesman 4',
                'default' => 0,
            ],
            [
                'kode_stok_owner' => 'SO005',
                'keterangan' => 'Salesman 5',
                'default' => 0,
            ],
        ];

        foreach ($stokOwners as $stokOwner) {
            StokOwner::create($stokOwner);
        }

        echo "✅ Data Stok Owner (Salesman) berhasil dibuat!\n";
        echo "Total Stok Owner: " . StokOwner::count() . "\n";
    }
}
