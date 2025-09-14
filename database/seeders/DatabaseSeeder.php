<?php

namespace Database\Seeders;

use App\Models\Supplier;
use App\Models\Customer;
use App\Models\Saldo;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Run permissions and roles seeder
        // $this->call(ImprovedPermissionSeeder::class);
        $this->call(PermissionSeeder::class);
        $this->call(WilayahSeeder::class);
        $this->call(RoleGroupSeeder::class);
        
        // Run user seeder
        $this->call(UserSeeder::class);
        
        // Run grup barang seeder
        $this->call(GrupBarangSeeder::class);
        
        // Run stok owner seeder
        $this->call(StokOwnerSeeder::class);
        
        // Create sample data
        $this->createSampleData();

        // Accounting seeders
        $this->call(AccountingPeriodSeeder::class);
        $this->call(ChartOfAccountsSeeder::class);
    }
    
    /**
     * Create sample data for the application
     */
    private function createSampleData()
    {
        // Create sample supplier
        Supplier::factory()->create([
            'kode_supplier' => 'SUP001',
            'nama' => 'Supplier A',
            'alamat' => '789 Supplier St, City, Country',
            'pemilik' => 'Owner A',
            'telepon_fax' => '021-4567890',
            'contact_person' => 'Contact Person A',
            'hp_contact_person' => '083234567890',
            'kode_grup_barang' => 'GRP001',
        ]);
        
        // Create sample customer
        Customer::factory()->create([
            'kode_customer' => 'CUST002',
            'nama' => 'Jane Smith',
            'alamat' => '456 Oak St, City, Country',
            'hp' => '082345678901',
            'telepon' => '021-34567890',
        ]);
        
        // Create sample saldo
        Saldo::factory()->create([
            'saldo' => 0,
            'room_rate' => 200000,
            'tax' => 0,
        ]);
    }
}