<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CaraBayar;

class CaraBayarSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            // Tunai
            ['metode' => 'Tunai', 'nama' => 'Cash (Kas Kecil)'],
            ['metode' => 'Tunai', 'nama' => 'Cash (Kas Besar)'],
            // Non Tunai
            ['metode' => 'Non Tunai', 'nama' => 'Kredit'],
            ['metode' => 'Non Tunai', 'nama' => 'BCA'],
            ['metode' => 'Non Tunai', 'nama' => 'BRI'],
            ['metode' => 'Non Tunai', 'nama' => 'MANDIRI'],
        ];

        foreach ($data as $row) {
            CaraBayar::updateOrCreate([
                'metode' => $row['metode'],
                'nama' => $row['nama'],
            ], []);
        }
    }
}
