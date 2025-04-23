<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CustomerSeeder extends Seeder
{
    public function run()
    {
        for ($i = 1; $i <= 10; $i++) {
            DB::table('customers')->insert([
                'kode_customer' => 'CUST' . $i,
                'nama' => 'Customer ' . $i,
                'alamat' => 'Alamat ' . $i,
                'hp' => '081234567' . $i,
                'telepon' => '021123456' . $i,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}