<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\AccountingService;
use App\Models\Journal;
use App\Models\Pembelian;
use App\Models\Transaksi;
use App\Models\Pembayaran;
use App\Models\PembayaranUtangSupplier;
use App\Models\ReturPenjualan;
use App\Models\ReturPembelian;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

Artisan::command('journals:smoke', function () {
    $this->info('Running AccountingService smoke tests...');
    $svc = app(AccountingService::class);

    $results = [];
    DB::beginTransaction();
    try {
        // 1) Sales (simulate minimal transaction object)
        $sale = new \stdClass();
        $sale->no_transaksi = 'SMOKE-SALE-'.now()->format('His');
        $sale->tanggal = now();
        $sale->cara_bayar = 'Kredit';
        $sale->pembayaran = 'Non Tunai';
        $sale->ppn = 11000; // assume DPP 100k, PPN 11k
        $sale->grand_total = 111000;
        $sale->items = collect(); // skip COGS if none
        $jr1 = $svc->createJournalFromSale($sale);
        $results[] = ['Sale', optional($jr1)->journal_no];

        // 2) Purchase (simulate minimal pembelian)
        $purchase = new \stdClass();
        $purchase->nota = 'SMOKE-PUR-'.now()->format('His');
        $purchase->tanggal = now();
        $purchase->pembayaran = 'Non Tunai';
        $purchase->cara_bayar = 'tempo';
        $purchase->ppn_total = 11000;
        $purchase->grand_total = 111000;
        $jr2 = $svc->createJournalFromPurchase($purchase);
        $results[] = ['Purchase', optional($jr2)->journal_no];

        // 3) AR Payment
        $payAr = new \stdClass();
        $payAr->no_pembayaran = 'SMOKE-PAYAR-'.now()->format('His');
        $payAr->tanggal = now();
        $payAr->total_bayar = 100000;
        $payAr->diskon = 5000; // test discount
        $jr3 = $svc->createJournalFromPaymentAR($payAr);
        $results[] = ['AR Payment', optional($jr3)->journal_no];

        // 4) AP Payment
        $payAp = new \stdClass();
        $payAp->no_pembayaran = 'SMOKE-PAYAP-'.now()->format('His');
        $payAp->tanggal = now();
        $payAp->total_bayar = 95000;
        $payAp->potongan = 5000; // test purchase discount
        $jr4 = $svc->createJournalFromPaymentAP($payAp);
        $results[] = ['AP Payment', optional($jr4)->journal_no];

        // 5) Sales Return (simple)
        $retSale = new \stdClass();
        $retSale->no_retur = 'SMOKE-RS-'.now()->format('His');
        $retSale->tanggal = now();
        $retSale->total_retur = 11100; // 10k dpp + 1.1k ppn
        $retSale->items = collect();
        $retSale->transaksi = (object)['ppn'=>11000,'grand_total'=>111000]; // provide ratio
        $jr5 = $svc->createJournalFromSalesReturn($retSale);
        $results[] = ['Sales Return', optional($jr5)->journal_no];

        // 6) Purchase Return (simple)
        $retPur = new \stdClass();
        $retPur->no_retur = 'SMOKE-RP-'.now()->format('His');
        $retPur->tanggal = now();
        $retPur->total_retur = 11100;
        $retPur->items = collect();
        $retPur->pembelian = (object)['ppn'=>11000,'grand_total'=>111000];
        $jr6 = $svc->createJournalFromPurchaseReturn($retPur);
        $results[] = ['Purchase Return', optional($jr6)->journal_no];

        // 7) Cash In
        $jr7 = $svc->createJournalCashIn(now()->format('Y-m-d'), 'SMOKE-CI', 12345);
        $results[] = ['Cash In', optional($jr7)->journal_no];

        // 8) Cash Out
        $jr8 = $svc->createJournalCashOut(now()->format('Y-m-d'), 'SMOKE-CO', 2345);
        $results[] = ['Cash Out', optional($jr8)->journal_no];

        // 9) Bank Loan Disbursement
        $jr9 = $svc->createJournalBankLoanDisbursement(now()->format('Y-m-d'), 'SMOKE-LOAN-DSB', 5000000);
        $results[] = ['Loan Disbursement', optional($jr9)->journal_no];

        // 10) Bank Loan Installment
        $jr10 = $svc->createJournalBankLoanInstallment(now()->format('Y-m-d'), 'SMOKE-LOAN-INS', 1000000, 50000);
        $results[] = ['Loan Installment', optional($jr10)->journal_no];

        DB::rollBack(); // do not persist by default; switch to commit if you want real data

        $this->table(['Scenario','Journal No'], $results);
        $this->info('Smoke test done (rolled back). To persist, change DB::rollBack() to DB::commit().');
    } catch (\Throwable $e) {
        DB::rollBack();
        Log::error('Smoke test failed', ['message'=>$e->getMessage()]);
        $this->error('Error: '.$e->getMessage());
    }
})->purpose('Run accounting journal smoke tests (non-persistent)');
