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
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();
        // Permission::create(['name' => 'view dashboard']);
        // Permission::create(['name' => 'edit users']);
        // Permission::create(['name' => 'access sales report']);

        Permission::create([
            'name' => 'view dashboard',
        ]);

        Permission::create([
            'name' => 'edit users',
        ]);

        Permission::create([
            'name' => 'access sales report',
        ]);

        $adminRole = Role::create(['name' => 'admin']);
        $adminRole->givePermissionTo(['view dashboard', 'edit users', 'access sales report']);

        User::factory()->create([
            'name' => 'Admin Bos',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('1234567890'),
        ])->assignRole('admin');

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
        


        Saldo::factory()->create([
            'saldo' => 0,
            'room_rate' => 200000,
            'tax' => 0,
        ]);
    }
}
