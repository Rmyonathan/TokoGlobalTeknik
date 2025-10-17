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
            
            // Non Tunai - Bank General
            ['metode' => 'Non Tunai', 'nama' => 'Kredit'],
            ['metode' => 'Non Tunai', 'nama' => 'BCA'],
            ['metode' => 'Non Tunai', 'nama' => 'BRI'],
            ['metode' => 'Non Tunai', 'nama' => 'MANDIRI'],
            ['metode' => 'Non Tunai', 'nama' => 'BNI'],
            
            // Bank Mandiri - QRIS, EDC, Giro
            ['metode' => 'Non Tunai', 'nama' => 'Bank Mandiri - QRIS'],
            ['metode' => 'Non Tunai', 'nama' => 'Bank Mandiri - EDC'],
            ['metode' => 'Non Tunai', 'nama' => 'Bank Mandiri - Giro'],
            
            // Bank BNI - QRIS, EDC, Giro
            ['metode' => 'Non Tunai', 'nama' => 'Bank BNI - QRIS'],
            ['metode' => 'Non Tunai', 'nama' => 'Bank BNI - EDC'],
            ['metode' => 'Non Tunai', 'nama' => 'Bank BNI - Giro'],
            
            // Bank BRI - QRIS, EDC, Giro
            ['metode' => 'Non Tunai', 'nama' => 'Bank BRI - QRIS'],
            ['metode' => 'Non Tunai', 'nama' => 'Bank BRI - EDC'],
            ['metode' => 'Non Tunai', 'nama' => 'Bank BRI - Giro'],
            
            // Bank BCA - QRIS, EDC, Giro
            ['metode' => 'Non Tunai', 'nama' => 'Bank BCA - QRIS'],
            ['metode' => 'Non Tunai', 'nama' => 'Bank BCA - EDC'],
            ['metode' => 'Non Tunai', 'nama' => 'Bank BCA - Giro'],
        ];

        foreach ($data as $row) {
            CaraBayar::updateOrCreate([
                'metode' => $row['metode'],
                'nama' => $row['nama'],
            ], []);
        }
    }
}
