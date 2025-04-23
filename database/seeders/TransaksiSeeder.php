<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TransaksiSeeder extends Seeder
{
    public function run()
    {
        for ($i = 1; $i <= 10; $i++) {
            DB::table('transaksi')->insert([
                'no_transaksi' => 'KP/WS/' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'tanggal' => now()->subDays(rand(0, 30))->format('Y-m-d'),
                'kode_customer' => 'CUST' . rand(1, 10),
                'sales' => 'Sales ' . rand(1, 5),
                'lokasi' => 'Cabang ' . rand(1, 3),
                'pembayaran' => ['tunai', 'tempo'][rand(0, 1)],
                'cara_bayar' => ['transfer', 'cash', 'debit'][rand(0, 2)],
                'tanggal_jadi' => now()->addDays(rand(1, 10))->format('Y-m-d'),
                'subtotal' => rand(100000, 500000),
                'discount' => rand(0, 10),
                'disc_rupiah' => rand(0, 5000),
                'ppn' => rand(0, 10000),
                'dp' => rand(0, 50000),
                'grand_total' => rand(100000, 500000),
                'status' => ['baru', 'selesai'][rand(0, 1)],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}