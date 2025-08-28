<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Wilayah;

class WilayahSeeder extends Seeder
{
    public function run(): void
    {
        $wilayahs = [
            [
                'nama_wilayah' => 'Jakarta Pusat',
                'keterangan' => 'Wilayah Jakarta Pusat dan sekitarnya'
            ],
            [
                'nama_wilayah' => 'Jakarta Barat',
                'keterangan' => 'Wilayah Jakarta Barat dan sekitarnya'
            ],
            [
                'nama_wilayah' => 'Jakarta Selatan',
                'keterangan' => 'Wilayah Jakarta Selatan dan sekitarnya'
            ],
            [
                'nama_wilayah' => 'Jakarta Timur',
                'keterangan' => 'Wilayah Jakarta Timur dan sekitarnya'
            ],
            [
                'nama_wilayah' => 'Jakarta Utara',
                'keterangan' => 'Wilayah Jakarta Utara dan sekitarnya'
            ],
            [
                'nama_wilayah' => 'Bekasi',
                'keterangan' => 'Wilayah Bekasi dan sekitarnya'
            ],
            [
                'nama_wilayah' => 'Depok',
                'keterangan' => 'Wilayah Depok dan sekitarnya'
            ],
            [
                'nama_wilayah' => 'Tangerang',
                'keterangan' => 'Wilayah Tangerang dan sekitarnya'
            ],
            [
                'nama_wilayah' => 'Bogor',
                'keterangan' => 'Wilayah Bogor dan sekitarnya'
            ],
            [
                'nama_wilayah' => 'Bandung',
                'keterangan' => 'Wilayah Bandung dan sekitarnya'
            ],
            [
                'nama_wilayah' => 'Surabaya',
                'keterangan' => 'Wilayah Surabaya dan sekitarnya'
            ],
            [
                'nama_wilayah' => 'Semarang',
                'keterangan' => 'Wilayah Semarang dan sekitarnya'
            ],
            [
                'nama_wilayah' => 'Yogyakarta',
                'keterangan' => 'Wilayah Yogyakarta dan sekitarnya'
            ],
            [
                'nama_wilayah' => 'Lainnya',
                'keterangan' => 'Wilayah lainnya di Indonesia'
            ]
        ];

        foreach ($wilayahs as $wilayah) {
            Wilayah::firstOrCreate(
                ['nama_wilayah' => $wilayah['nama_wilayah']],
                $wilayah
            );
        }

        $this->command->info('✅ Data wilayah berhasil dibuat!');
    }
}
