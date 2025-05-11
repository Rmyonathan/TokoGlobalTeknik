<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Supplier;
use App\Models\Customer;
use App\Models\Rooms;
use App\Models\Saldo;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Admin Bos',
            'email' => 'admin@gmail.com',
            'role' => 'admin',
            'password' => Hash::make('1234567890'),
        ]);

        User::factory()->create([
            'name' => 'Pegawai X',
            'email' => 'employee1@gmail.com',
            'role' => 'first',
            'password' => Hash::make('1234567890'),
        ]);

        User::factory()->create([
            'name' => 'Pegawai Y',
            'email' => 'employee2@gmail.com',
            'role' => 'second',
            'password' => Hash::make('1234567890'),
        ]);

        User::factory()->create([
            'name' => 'Pegawai Z',
            'email' => 'employee3@gmail.com',
            'role' => 'third',
            'password' => Hash::make('1234567890'),
        ]);

        Supplier::factory()->create([
            'kode_supplier' => 'SUP001',
            'nama' => 'Supplier A',
            'alamat' => '789 Supplier St, City, Country',
            'pemilik' => 'Owner A',
            'telepon_fax' => '021-4567890',
            'contact_person' => 'Contact Person A',
            'hp_contact_person' => '083234567890',
            'kode_kategori' => 'CAT001',
        ]);

        Customer::factory()->create([
            'kode_customer' => 'CUST002',
            'nama' => 'Jane Smith',
            'alamat' => '456 Oak St, City, Country',
            'hp' => '082345678901',
            'telepon' => '021-34567890',
        ]);

        // name	description	price	stocks	auto_stock	auto_stock_value
        // Items::factory()->create([
        //     'name' => 'Pegawai',
        //     'description' => '-',
        //     'price' => 'user',
        //     'stocks' => ,
        //     'price' => 'user',
        //     'password' => Hash::make('1234567890'),
        // ]);



        // Rooms::factory()->create([
        //     'room_number' => '001',
        //     'price_per_night' => 200000,
        //     'status' => 'vacant',
        // ]);

        // Rooms::factory()->create([
        //     'room_number' => '002',
        //     'price_per_night' => 200000,
        //     'status' => 'vacant',
        // ]);

        // Rooms::factory()->create([
        //     'room_number' => '003',
        //     'price_per_night' => 200000,
        //     'status' => 'vacant',
        // ]);

        // Rooms::factory()->create([
        //     'room_number' => '004',
        //     'price_per_night' => 200000,
        //     'status' => 'vacant',
        // ]);

        // Rooms::factory()->create([
        //     'room_number' => '005',
        //     'price_per_night' => 200000,
        //     'status' => 'vacant',
        // ]);

        // Rooms::factory()->create([
        //     'room_number' => '006',
        //     'price_per_night' => 200000,
        //     'status' => 'vacant',
        // ]);

        // Rooms::factory()->create([
        //     'room_number' => '007',
        //     'price_per_night' => 200000,
        //     'status' => 'vacant',
        // ]);

        // Rooms::factory()->create([
        //     'room_number' => '008',
        //     'price_per_night' => 200000,
        //     'status' => 'vacant',
        // ]);

        // Rooms::factory()->create([
        //     'room_number' => '009',
        //     'price_per_night' => 200000,
        //     'status' => 'vacant',
        // ]);
        // Rooms::factory()->create([
        //     'room_number' => '010',
        //     'price_per_night' => 200000,
        //     'status' => 'vacant',
        // ]);

        // Rooms::factory()->create([
        //     'room_number' => '011',
        //     'price_per_night' => 200000,
        //     'status' => 'vacant',
        // ]);

        // Rooms::factory()->create([
        //     'room_number' => '012',
        //     'price_per_night' => 200000,
        //     'status' => 'vacant',
        // ]);

        // Rooms::factory()->create([
        //     'room_number' => '013',
        //     'price_per_night' => 200000,
        //     'status' => 'vacant',
        // ]);

        // Rooms::factory()->create([
        //     'room_number' => '014',
        //     'price_per_night' => 200000,
        //     'status' => 'vacant',
        // ]);

        // Rooms::factory()->create([
        //     'room_number' => '015',
        //     'price_per_night' => 200000,
        //     'status' => 'vacant',
        // ]);

        // Rooms::factory()->create([
        //     'room_number' => '016',
        //     'price_per_night' => 200000,
        //     'status' => 'vacant',
        // ]);

        Saldo::factory()->create([
            'saldo' => 0,
            'room_rate' => 200000,
            'tax' => 0,
        ]);
    }
}
