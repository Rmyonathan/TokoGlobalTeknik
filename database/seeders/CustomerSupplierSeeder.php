<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Supplier;
use App\Models\Wilayah;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CustomerSupplierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clean existing data
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        try { DB::table('customers')->truncate(); } catch (\Throwable $e) {}
        try { DB::table('suppliers')->truncate(); } catch (\Throwable $e) {}
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        // Get or create wilayah
        $wilayah = Wilayah::firstOrCreate(
            ['nama_wilayah' => 'Jakarta'],
            ['nama_wilayah' => 'Jakarta', 'keterangan' => 'Wilayah Jakarta', 'is_active' => true]
        );

        // Create Customers
        $customers = [
            [
                'kode_customer' => 'CUST001',
                'nama' => 'PT Plastik Maju',
                'alamat' => 'Jl. Raya Industri No. 123, Jakarta',
                'telepon' => '021-1234567',
                'hp' => '081234567890',
                'wilayah_id' => $wilayah->id,
                'limit_kredit' => 50000000,
                'sisa_kredit' => 50000000,
                'is_active' => true
            ],
            [
                'kode_customer' => 'CUST002',
                'nama' => 'CV Kemasan Sejahtera',
                'alamat' => 'Jl. Perdagangan No. 456, Jakarta',
                'telepon' => '021-2345678',
                'hp' => '081234567891',
                'wilayah_id' => $wilayah->id,
                'limit_kredit' => 30000000,
                'sisa_kredit' => 30000000,
                'is_active' => true
            ],
            [
                'kode_customer' => 'CUST003',
                'nama' => 'UD Sumber Plastik',
                'alamat' => 'Jl. Pasar Raya No. 789, Jakarta',
                'telepon' => '021-3456789',
                'hp' => '081234567892',
                'wilayah_id' => $wilayah->id,
                'limit_kredit' => 20000000,
                'sisa_kredit' => 20000000,
                'is_active' => true
            ],
            [
                'kode_customer' => 'CUST004',
                'nama' => 'PT Industri Plastik',
                'alamat' => 'Jl. Kawasan Industri No. 321, Jakarta',
                'telepon' => '021-4567890',
                'hp' => '081234567893',
                'wilayah_id' => $wilayah->id,
                'limit_kredit' => 75000000,
                'sisa_kredit' => 75000000,
                'is_active' => true
            ],
            [
                'kode_customer' => 'CUST005',
                'nama' => 'Toko Plastik Mandiri',
                'alamat' => 'Jl. Raya Utara No. 654, Jakarta',
                'telepon' => '021-5678901',
                'hp' => '081234567894',
                'wilayah_id' => $wilayah->id,
                'limit_kredit' => 15000000,
                'sisa_kredit' => 15000000,
                'is_active' => true
            ],
        ];

        foreach ($customers as $customer) {
            Customer::create($customer);
        }

        // Create Suppliers
        $suppliers = [
            [
                'kode_supplier' => 'SUPP001',
                'nama' => 'PT Plastik Indonesia',
                'alamat' => 'Jl. Industri Plastik No. 100, Jakarta',
                'pemilik' => 'John Doe',
                'telepon_fax' => '021-1111111',
                'contact_person' => 'Jane Smith',
                'hp_contact_person' => '081111111111',
                'kode_grup_barang' => 'GRP001',
                'is_active' => true
            ],
            [
                'kode_supplier' => 'SUPP002',
                'nama' => 'CV Bahan Baku Plastik',
                'alamat' => 'Jl. Supplier Raya No. 200, Jakarta',
                'pemilik' => 'Bob Johnson',
                'telepon_fax' => '021-2222222',
                'contact_person' => 'Alice Brown',
                'hp_contact_person' => '082222222222',
                'kode_grup_barang' => 'GRP002',
                'is_active' => true
            ],
            [
                'kode_supplier' => 'SUPP003',
                'nama' => 'PT Distributor Plastik',
                'alamat' => 'Jl. Distribusi No. 300, Jakarta',
                'pemilik' => 'Charlie Wilson',
                'telepon_fax' => '021-3333333',
                'contact_person' => 'Diana Davis',
                'hp_contact_person' => '083333333333',
                'kode_grup_barang' => 'GRP003',
                'is_active' => true
            ],
            [
                'kode_supplier' => 'SUPP004',
                'nama' => 'UD Grosir Plastik',
                'alamat' => 'Jl. Grosir Plastik No. 400, Jakarta',
                'pemilik' => 'Eve Miller',
                'telepon_fax' => '021-4444444',
                'contact_person' => 'Frank Garcia',
                'hp_contact_person' => '084444444444',
                'kode_grup_barang' => 'GRP004',
                'is_active' => true
            ],
            [
                'kode_supplier' => 'SUPP005',
                'nama' => 'PT Importir Plastik',
                'alamat' => 'Jl. Import Plastik No. 500, Jakarta',
                'pemilik' => 'Grace Lee',
                'telepon_fax' => '021-5555555',
                'contact_person' => 'Henry Taylor',
                'hp_contact_person' => '085555555555',
                'kode_grup_barang' => 'GRP005',
                'is_active' => true
            ],
        ];

        foreach ($suppliers as $supplier) {
            Supplier::create($supplier);
        }

        $this->command->info('✅ Data customer dan supplier berhasil dibuat!');
        $this->command->info('Total customers: ' . count($customers));
        $this->command->info('Total suppliers: ' . count($suppliers));
    }
}
