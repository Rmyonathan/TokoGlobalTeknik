<?php

namespace Database\Seeders;

use App\Models\AccountType;
use App\Models\ChartOfAccount;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ChartOfAccountsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Bersihkan data lama agar seed konsisten
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        // Truncate tabel yang berelasi ke COA terlebih dahulu
        try { DB::table('journal_details')->truncate(); } catch (\Throwable $e) {}
        try { DB::table('journals')->truncate(); } catch (\Throwable $e) {}
        try { DB::table('chart_of_accounts')->truncate(); } catch (\Throwable $e) {}
        try { DB::table('account_types')->truncate(); } catch (\Throwable $e) {}
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $types = [
            ['code' => 'A', 'name' => 'Assets', 'normal_balance' => 'D'],
            ['code' => 'L', 'name' => 'Liabilities', 'normal_balance' => 'C'],
            ['code' => 'E', 'name' => 'Equity', 'normal_balance' => 'C'],
            ['code' => 'R', 'name' => 'Revenue', 'normal_balance' => 'C'],
            ['code' => 'X', 'name' => 'Expense', 'normal_balance' => 'D'],
        ];

        $typeIds = [];
        foreach ($types as $type) {
            $typeIds[$type['code']] = AccountType::firstOrCreate(
                ['code' => $type['code']],
                ['name' => $type['name'], 'normal_balance' => $type['normal_balance']]
            )->id;
        }

        $accounts = [
            // 1. ASET
            ['code' => '1100', 'name' => 'Aset Lancar', 'type' => 'A', 'parent' => null],
            ['code' => '1101', 'name' => 'Kas Kecil', 'type' => 'A', 'parent' => '1100'],
            ['code' => '1102', 'name' => 'Kas Besar', 'type' => 'A', 'parent' => '1100'],
            ['code' => '1103', 'name' => 'Piutang Usaha', 'type' => 'A', 'parent' => '1100'],
            ['code' => '1104', 'name' => 'Bank', 'type' => 'A', 'parent' => '1100'],
            // Sub-akun bank per nama (format 1104-x)
            ['code' => '1104-1', 'name' => 'Bank Mandiri', 'type' => 'A', 'parent' => '1104'],
            ['code' => '1104-2', 'name' => 'Bank BNI', 'type' => 'A', 'parent' => '1104'],
            ['code' => '1104-3', 'name' => 'Bank BRI', 'type' => 'A', 'parent' => '1104'],
            ['code' => '1104-4', 'name' => 'Bank BCA', 'type' => 'A', 'parent' => '1104'],
            ['code' => '1105', 'name' => 'Persediaan Barang Dagang', 'type' => 'A', 'parent' => '1100'],
            ['code' => '1106', 'name' => 'PPN Masukan', 'type' => 'A', 'parent' => '1100'],
            ['code' => '1107', 'name' => 'Persediaan Transit (Intercompany Aset)', 'type' => 'A', 'parent' => '1100'],
            ['code' => '1200', 'name' => 'Aset Tetap', 'type' => 'A', 'parent' => null],
            ['code' => '1201', 'name' => 'Peralatan Toko', 'type' => 'A', 'parent' => '1200'],
            ['code' => '1202', 'name' => 'Akumulasi Penyusutan - Peralatan', 'type' => 'A', 'parent' => '1200'],

            // 2. LIABILITAS
            ['code' => '2100', 'name' => 'Liabilitas Jangka Pendek', 'type' => 'L', 'parent' => null],
            ['code' => '2101', 'name' => 'Utang Usaha', 'type' => 'L', 'parent' => '2100'],
            ['code' => '2102', 'name' => 'PPN Keluaran', 'type' => 'L', 'parent' => '2100'],
            ['code' => '2103', 'name' => 'Utang Bank', 'type' => 'L', 'parent' => '2100'],
            ['code' => '2104', 'name' => 'Hutang Transit', 'type' => 'L', 'parent' => '2100'],
            
            // 2.1. LIABILITAS TOKO 2 (Database Kedua)
            ['code' => '2200', 'name' => 'Liabilitas Toko 2', 'type' => 'L', 'parent' => null],
            ['code' => '2201', 'name' => 'Piutang PPN Toko 2', 'type' => 'A', 'parent' => '2200'],
            ['code' => '2202', 'name' => 'Utang PPN Toko 2', 'type' => 'L', 'parent' => '2200'],

            // 3. EKUITAS
            ['code' => '3101', 'name' => 'Modal Pemilik', 'type' => 'E', 'parent' => null],
            ['code' => '3102', 'name' => 'Laba Ditahan', 'type' => 'E', 'parent' => null],

            // 4. PENDAPATAN
            ['code' => '4101', 'name' => 'Pendapatan Penjualan', 'type' => 'R', 'parent' => null],
            ['code' => '4800', 'name' => 'Pendapatan Lain-lain', 'type' => 'R', 'parent' => null],
            ['code' => '4102', 'name' => 'Retur Penjualan', 'type' => 'R', 'parent' => '4101'],

            // 5. BEBAN
            ['code' => '5100', 'name' => 'Harga Pokok Penjualan (HPP)', 'type' => 'X', 'parent' => null],
            ['code' => '5200', 'name' => 'Beban Operasional', 'type' => 'X', 'parent' => null],
            ['code' => '5205', 'name' => 'Diskon Pembelian', 'type' => 'X', 'parent' => '5200'],
            ['code' => '5206', 'name' => 'Diskon Penjualan', 'type' => 'X', 'parent' => '5200'],
            ['code' => '5900', 'name' => 'Beban Lain-lain', 'type' => 'X', 'parent' => null],
            ['code' => '5201', 'name' => 'Beban Gaji Karyawan', 'type' => 'X', 'parent' => '5200'],
            ['code' => '5202', 'name' => 'Beban Listrik, Air, & Telepon', 'type' => 'X', 'parent' => '5200'],
            ['code' => '5203', 'name' => 'Beban Sewa Toko', 'type' => 'X', 'parent' => '5200'],
            ['code' => '5204', 'name' => 'Beban Penyusutan - Peralatan', 'type' => 'X', 'parent' => '5200'],
        ];

        $codeToId = [];
        foreach ($accounts as $acc) {
            $parentId = $acc['parent'] ? ($codeToId[$acc['parent']] ?? ChartOfAccount::where('code', $acc['parent'])->value('id')) : null;
            $account = ChartOfAccount::firstOrCreate(
                ['code' => $acc['code']],
                [
                    'name' => $acc['name'],
                    'account_type_id' => $typeIds[$acc['type']],
                    'parent_id' => $parentId,
                    'is_active' => true,
                ]
            );
            $codeToId[$acc['code']] = $account->id;
        }
    }
}
