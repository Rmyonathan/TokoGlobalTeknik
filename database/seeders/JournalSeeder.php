<?php

namespace Database\Seeders;

use App\Models\Journal;
use App\Models\JournalDetail;
use App\Models\ChartOfAccount;
use App\Models\AccountingPeriod;
use App\Services\AccountingService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class JournalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clean existing data
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        try { DB::table('journal_details')->truncate(); } catch (\Throwable $e) {}
        try { DB::table('journals')->truncate(); } catch (\Throwable $e) {}
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $accountingService = new AccountingService();
        $period = AccountingPeriod::where('is_closed', false)->first();
        
        if (!$period) {
            $this->command->warn('⚠️  Tidak ada periode akuntansi aktif. Jalankan AccountingPeriodSeeder terlebih dahulu.');
            return;
        }

        // Get required accounts
        $kasBesar = ChartOfAccount::where('name', 'Kas Besar')->first();
        $kasKecil = ChartOfAccount::where('name', 'Kas Kecil')->first();
        $bankBca = ChartOfAccount::where('name', 'Bank BCA')->first();
        $bankMandiri = ChartOfAccount::where('name', 'Bank Mandiri')->first();
        $piutangUsaha = ChartOfAccount::where('name', 'Piutang Usaha')->first();
        $utangUsaha = ChartOfAccount::where('name', 'Utang Usaha')->first();
        $pendapatanPenjualan = ChartOfAccount::where('name', 'Pendapatan Penjualan')->first();
        $hpp = ChartOfAccount::where('name', 'Harga Pokok Penjualan (HPP)')->first();
        $persediaan = ChartOfAccount::where('name', 'Persediaan Barang Dagang')->first();
        $ppnKeluaran = ChartOfAccount::where('name', 'PPN Keluaran')->first();
        $ppnMasukan = ChartOfAccount::where('name', 'PPN Masukan')->first();
        $pendapatanLain = ChartOfAccount::where('name', 'Pendapatan Lain-lain')->first();
        $bebanLain = ChartOfAccount::where('name', 'Beban Lain-lain')->first();
        $diskonPenjualan = ChartOfAccount::where('name', 'Diskon Penjualan')->first();
        $diskonPembelian = ChartOfAccount::where('name', 'Diskon Pembelian')->first();

        if (!$kasBesar || !$pendapatanPenjualan || !$persediaan) {
            $this->command->warn('⚠️  COA belum lengkap. Jalankan ChartOfAccountsSeeder terlebih dahulu.');
            return;
        }

        $journalData = [];

        // 1. Sales Journal (Penjualan Tunai)
        $salesJournal = $accountingService->createJournal(
            now()->subDays(5)->format('Y-m-d'),
            'SALE-001',
            'Jurnal Penjualan Tunai',
            [
                ['account_id' => $kasBesar->id, 'debit' => 1000000, 'credit' => 0, 'memo' => 'Penerimaan penjualan tunai'],
                ['account_id' => $pendapatanPenjualan->id, 'debit' => 0, 'credit' => 900000, 'memo' => 'Pendapatan penjualan'],
                ['account_id' => $ppnKeluaran->id, 'debit' => 0, 'credit' => 100000, 'memo' => 'PPN Keluaran'],
                ['account_id' => $hpp->id, 'debit' => 600000, 'credit' => 0, 'memo' => 'HPP'],
                ['account_id' => $persediaan->id, 'debit' => 0, 'credit' => 600000, 'memo' => 'Pengurangan persediaan'],
            ],
            $period->id
        );
        if ($salesJournal) $journalData[] = ['type' => 'Penjualan Tunai', 'amount' => 1000000];

        // 2. Sales Journal (Penjualan Kredit)
        $creditSalesJournal = $accountingService->createJournal(
            now()->subDays(4)->format('Y-m-d'),
            'SALE-002',
            'Jurnal Penjualan Kredit',
            [
                ['account_id' => $piutangUsaha->id, 'debit' => 1500000, 'credit' => 0, 'memo' => 'Piutang usaha penjualan'],
                ['account_id' => $pendapatanPenjualan->id, 'debit' => 0, 'credit' => 1350000, 'memo' => 'Pendapatan penjualan'],
                ['account_id' => $ppnKeluaran->id, 'debit' => 0, 'credit' => 150000, 'memo' => 'PPN Keluaran'],
                ['account_id' => $hpp->id, 'debit' => 900000, 'credit' => 0, 'memo' => 'HPP'],
                ['account_id' => $persediaan->id, 'debit' => 0, 'credit' => 900000, 'memo' => 'Pengurangan persediaan'],
            ],
            $period->id
        );
        if ($creditSalesJournal) $journalData[] = ['type' => 'Penjualan Kredit', 'amount' => 1500000];

        // 3. Purchase Journal (Pembelian Tunai)
        $purchaseJournal = $accountingService->createJournal(
            now()->subDays(3)->format('Y-m-d'),
            'PUR-001',
            'Jurnal Pembelian Tunai',
            [
                ['account_id' => $persediaan->id, 'debit' => 800000, 'credit' => 0, 'memo' => 'Persediaan dari pembelian'],
                ['account_id' => $ppnMasukan->id, 'debit' => 88000, 'credit' => 0, 'memo' => 'PPN Masukan'],
                ['account_id' => $kasBesar->id, 'debit' => 0, 'credit' => 888000, 'memo' => 'Pembayaran pembelian'],
            ],
            $period->id
        );
        if ($purchaseJournal) $journalData[] = ['type' => 'Pembelian Tunai', 'amount' => 888000];

        // 4. Purchase Journal (Pembelian Kredit)
        $creditPurchaseJournal = $accountingService->createJournal(
            now()->subDays(2)->format('Y-m-d'),
            'PUR-002',
            'Jurnal Pembelian Kredit',
            [
                ['account_id' => $persediaan->id, 'debit' => 1200000, 'credit' => 0, 'memo' => 'Persediaan dari pembelian'],
                ['account_id' => $ppnMasukan->id, 'debit' => 132000, 'credit' => 0, 'memo' => 'PPN Masukan'],
                ['account_id' => $utangUsaha->id, 'debit' => 0, 'credit' => 1332000, 'memo' => 'Utang usaha pembelian'],
            ],
            $period->id
        );
        if ($creditPurchaseJournal) $journalData[] = ['type' => 'Pembelian Kredit', 'amount' => 1332000];

        // 5. AR Payment Journal
        $arPaymentJournal = $accountingService->createJournal(
            now()->subDays(1)->format('Y-m-d'),
            'PAY-AR-001',
            'Pembayaran Piutang',
            [
                ['account_id' => $bankBca->id, 'debit' => 750000, 'credit' => 0, 'memo' => 'Terima pembayaran pelanggan'],
                ['account_id' => $piutangUsaha->id, 'debit' => 0, 'credit' => 750000, 'memo' => 'Pelunasan piutang'],
            ],
            $period->id
        );
        if ($arPaymentJournal) $journalData[] = ['type' => 'Pembayaran Piutang', 'amount' => 750000];

        // 6. AP Payment Journal
        $apPaymentJournal = $accountingService->createJournal(
            now()->format('Y-m-d'),
            'PAY-AP-001',
            'Pembayaran Utang',
            [
                ['account_id' => $utangUsaha->id, 'debit' => 1000000, 'credit' => 0, 'memo' => 'Pelunasan utang'],
                ['account_id' => $bankMandiri->id, 'debit' => 0, 'credit' => 1000000, 'memo' => 'Pembayaran kepada supplier'],
            ],
            $period->id
        );
        if ($apPaymentJournal) $journalData[] = ['type' => 'Pembayaran Utang', 'amount' => 1000000];

        // 7. Sales Return Journal
        $salesReturnJournal = $accountingService->createJournal(
            now()->subDays(1)->format('Y-m-d'),
            'RET-SALE-001',
            'Retur Penjualan',
            [
                ['account_id' => $pendapatanPenjualan->id, 'debit' => 200000, 'credit' => 0, 'memo' => 'Retur penjualan'],
                ['account_id' => $ppnKeluaran->id, 'debit' => 22000, 'credit' => 0, 'memo' => 'Pembalikan PPN Keluaran'],
                ['account_id' => $piutangUsaha->id, 'debit' => 0, 'credit' => 222000, 'memo' => 'Koreksi piutang'],
                ['account_id' => $persediaan->id, 'debit' => 120000, 'credit' => 0, 'memo' => 'Barang retur masuk ke persediaan'],
                ['account_id' => $hpp->id, 'debit' => 0, 'credit' => 120000, 'memo' => 'Pembalikan HPP atas retur'],
            ],
            $period->id
        );
        if ($salesReturnJournal) $journalData[] = ['type' => 'Retur Penjualan', 'amount' => 222000];

        // 8. Purchase Return Journal
        $purchaseReturnJournal = $accountingService->createJournal(
            now()->format('Y-m-d'),
            'RET-PUR-001',
            'Retur Pembelian',
            [
                ['account_id' => $utangUsaha->id, 'debit' => 300000, 'credit' => 0, 'memo' => 'Koreksi utang karena retur pembelian'],
                ['account_id' => $pendapatanPenjualan->id, 'debit' => 0, 'credit' => 270000, 'memo' => 'Retur pembelian'],
                ['account_id' => $ppnMasukan->id, 'debit' => 0, 'credit' => 30000, 'memo' => 'Pembalikan PPN Masukan'],
                ['account_id' => $persediaan->id, 'debit' => 0, 'credit' => 180000, 'memo' => 'Pengurangan persediaan karena retur'],
            ],
            $period->id
        );
        if ($purchaseReturnJournal) $journalData[] = ['type' => 'Retur Pembelian', 'amount' => 300000];

        // 9. Cash In Journal
        $cashInJournal = $accountingService->createJournal(
            now()->format('Y-m-d'),
            'CASH-IN-001',
            'Kas Masuk Lainnya',
            [
                ['account_id' => $kasKecil->id, 'debit' => 500000, 'credit' => 0, 'memo' => 'Kas masuk lainnya'],
                ['account_id' => $pendapatanLain->id, 'debit' => 0, 'credit' => 500000, 'memo' => 'Pendapatan lain-lain'],
            ],
            $period->id
        );
        if ($cashInJournal) $journalData[] = ['type' => 'Kas Masuk Lainnya', 'amount' => 500000];

        // 10. Cash Out Journal
        $cashOutJournal = $accountingService->createJournal(
            now()->format('Y-m-d'),
            'CASH-OUT-001',
            'Kas Keluar Biaya',
            [
                ['account_id' => $bebanLain->id, 'debit' => 250000, 'credit' => 0, 'memo' => 'Beban kas keluar'],
                ['account_id' => $kasBesar->id, 'debit' => 0, 'credit' => 250000, 'memo' => 'Kas keluar'],
            ],
            $period->id
        );
        if ($cashOutJournal) $journalData[] = ['type' => 'Kas Keluar Biaya', 'amount' => 250000];

        $this->command->info('✅ Data jurnal berhasil dibuat!');
        $this->command->info('Total jurnal: ' . count($journalData));
        $this->command->info('Total nilai jurnal: Rp ' . number_format(collect($journalData)->sum('amount'), 0, ',', '.'));
        
        // Show sample data
        $this->command->info("\n📋 Sample journals:");
        foreach (array_slice($journalData, 0, 5) as $data) {
            $this->command->info("- {$data['type']} | Rp " . number_format($data['amount'], 0, ',', '.'));
        }
    }
}
