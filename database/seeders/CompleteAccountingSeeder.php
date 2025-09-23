<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CompleteAccountingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * This seeder runs all accounting-related seeders in the correct order
     * to create a complete accounting system with sample data.
     */
    public function run(): void
    {
        $this->command->info('🚀 Starting Complete Accounting Seeder...');
        $this->command->info('=====================================');

        // Clean existing data first
        $this->command->info('🧹 Cleaning existing data...');
        $this->cleanExistingData();
        $this->command->info('✅ Data cleaned');

        // Step 1: Basic setup
        $this->command->info('📋 Step 1: Setting up basic data...');
        $this->call([
            WilayahSeeder::class,
            // UserSeeder::class, // Skip if users already exist
            AccountingPeriodSeeder::class,
            ChartOfAccountsSeeder::class,
        ]);
        $this->command->info('✅ Basic setup completed');

        // Step 2: Master data
        $this->command->info('📋 Step 2: Creating master data...');
        $this->call([
            BarangSeeder::class,
            CustomerSupplierSeeder::class,
        ]);
        $this->command->info('✅ Master data completed');

        // Step 3: Transaction data
        $this->command->info('📋 Step 3: Creating transaction data...');
        $this->call([
            PembelianSeeder::class,
            PenjualanSeeder::class,
        ]);
        $this->command->info('✅ Transaction data completed');

        // Step 4: Return data
        $this->command->info('📋 Step 4: Creating return data...');
        $this->call([
            ReturSeeder::class,
        ]);
        $this->command->info('✅ Return data completed');

        // Step 5: Payment data
        $this->command->info('📋 Step 5: Creating payment data...');
        $this->call([
            PembayaranSeeder::class,
        ]);
        $this->command->info('✅ Payment data completed');

        // Step 6: Journal data
        $this->command->info('📋 Step 6: Creating journal data...');
        $this->call([
            JournalSeeder::class,
        ]);
        $this->command->info('✅ Journal data completed');

        // Step 7: Summary
        $this->command->info('=====================================');
        $this->command->info('🎉 Complete Accounting Seeder finished!');
        $this->command->info('');
        $this->command->info('📊 Data Summary:');
        $this->showDataSummary();
        $this->command->info('');
        $this->command->info('🔧 Next Steps:');
        $this->command->info('1. Test the accounting flows in the application');
        $this->command->info('2. Verify journal entries are balanced');
        $this->command->info('3. Check COA balances are updated correctly');
        $this->command->info('4. Test PPN functionality (DB2 only)');
        $this->command->info('5. Test intercompany transfers');
    }

    private function cleanExistingData(): void
    {
        try {
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
            
            // Clean transaction data
            DB::table('transaksi_item_sumber')->truncate();
            DB::table('transaksi_items')->truncate();
            DB::table('transaksi')->truncate();
            
            // Clean purchase data
            DB::table('pembelian_items')->truncate();
            DB::table('pembelian')->truncate();
            
            // Clean return data
            DB::table('retur_penjualan_items')->truncate();
            DB::table('retur_penjualan')->truncate();
            DB::table('retur_pembelian_items')->truncate();
            DB::table('retur_pembelian')->truncate();
            
            // Clean payment data
            DB::table('pembayaran_utang_supplier_nota_debits')->truncate();
            DB::table('pembayaran_utang_supplier_details')->truncate();
            DB::table('pembayaran_utang_suppliers')->truncate();
            DB::table('pembayaran_piutang_nota_kredits')->truncate();
            DB::table('pembayaran_details')->truncate();
            DB::table('pembayarans')->truncate();
            
            // Clean journal data
            DB::table('journal_details')->truncate();
            DB::table('journals')->truncate();
            
            // Clean stock data
            DB::table('stock_batches')->truncate();
            DB::table('stocks')->truncate();
            DB::table('kode_barangs')->truncate();
            DB::table('grup_barang')->truncate();
            
            // Clean master data
            DB::table('customers')->truncate();
            DB::table('suppliers')->truncate();
            
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        } catch (\Exception $e) {
            $this->command->warn('⚠️  Some data could not be cleaned: ' . $e->getMessage());
        }
    }

    private function showDataSummary(): void
    {
        try {
            // Count records
            $counts = [
                'Users' => DB::table('users')->count(),
                'Chart of Accounts' => DB::table('chart_of_accounts')->count(),
                'Accounting Periods' => DB::table('accounting_periods')->count(),
                'Grup Barang' => DB::table('grup_barang')->count(),
                'Kode Barang' => DB::table('kode_barangs')->count(),
                'Stock Records' => DB::table('stocks')->count(),
                'Stock Batches' => DB::table('stock_batches')->count(),
                'Customers' => DB::table('customers')->count(),
                'Suppliers' => DB::table('suppliers')->count(),
                'Sales Transactions' => DB::table('transaksi')->count(),
                'Purchase Transactions' => DB::table('pembelian')->count(),
                'Sales Returns' => DB::table('retur_penjualan')->count(),
                'Purchase Returns' => DB::table('retur_pembelian')->count(),
                'AR Payments' => DB::table('pembayarans')->count(),
                'AP Payments' => DB::table('pembayaran_utang_suppliers')->count(),
                'Journals' => DB::table('journals')->count(),
                'Journal Details' => DB::table('journal_details')->count(),
            ];

            foreach ($counts as $table => $count) {
                $this->command->info("  • {$table}: {$count}");
            }

            // Show financial summary
            $totalSales = DB::table('transaksi')->sum('grand_total') ?? 0;
            $totalPurchases = DB::table('pembelian')->sum('grand_total') ?? 0;
            $totalARPayments = DB::table('pembayarans')->sum('total_bayar') ?? 0;
            $totalAPPayments = DB::table('pembayaran_utang_suppliers')->sum('total_bayar') ?? 0;

            $this->command->info('');
            $this->command->info('💰 Financial Summary:');
            $this->command->info('  • Total Sales: Rp ' . number_format($totalSales, 0, ',', '.'));
            $this->command->info('  • Total Purchases: Rp ' . number_format($totalPurchases, 0, ',', '.'));
            $this->command->info('  • Total AR Payments: Rp ' . number_format($totalARPayments, 0, ',', '.'));
            $this->command->info('  • Total AP Payments: Rp ' . number_format($totalAPPayments, 0, ',', '.'));

        } catch (\Exception $e) {
            $this->command->warn('⚠️  Could not generate data summary: ' . $e->getMessage());
        }
    }
}
