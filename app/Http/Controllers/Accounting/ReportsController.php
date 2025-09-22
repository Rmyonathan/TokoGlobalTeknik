<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\ChartOfAccount;
use App\Models\Journal;
use App\Models\JournalDetail;
use App\Models\AccountingPeriod;
use App\Models\ReportHistory;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportsController extends Controller
{
    public function generalLedger(Request $request)
    {
        $from = $request->get('from', now()->startOfMonth()->toDateString());
        $to = $request->get('to', now()->endOfMonth()->toDateString());
        $accountId = $request->get('account_id');
        $accounts = ChartOfAccount::orderBy('code')->get();

        $entries = collect();
        $account = null;
        if ($accountId) {
            $account = ChartOfAccount::find($accountId);
            $entries = JournalDetail::with(['journal'])
                ->where('account_id', $accountId)
                ->whereHas('journal', function($q) use ($from, $to){
                    $q->whereBetween('journal_date', [$from, $to]);
                })
                ->orderBy('journal_id')
                ->orderBy('id')
                ->get();
        }

        // Export handlers
        $export = $request->get('export');
        if ($export && $accountId) {
            if ($export === 'csv') {
                return $this->exportGeneralLedgerCsv($entries, $account, $from, $to);
            } elseif ($export === 'pdf') {
                return $this->exportGeneralLedgerPdf($entries, $account, $from, $to);
            }
        }

        return view('accounting.reports.general_ledger', compact('accounts','entries','from','to','accountId'));
    }

    private function exportGeneralLedgerCsv($entries, ?ChartOfAccount $account, string $from, string $to): StreamedResponse
    {
        $filename = 'general_ledger_'.$account->code.'_'.$from.'_to_'.$to.'.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ];

        $callback = function() use ($entries, $account, $from, $to) {
            $handle = fopen('php://output', 'w');
            // Header info row
            fputcsv($handle, ['Buku Besar (General Ledger)']);
            fputcsv($handle, ['Akun', $account->code.' - '.$account->name]);
            fputcsv($handle, ['Periode', $from.' s/d '.$to]);
            fputcsv($handle, []);
            // Table header
            fputcsv($handle, ['Tanggal', 'No. Jurnal', 'Referensi', 'Keterangan', 'Debet', 'Kredit']);
            $totalD = 0; $totalK = 0;
            foreach ($entries as $e) {
                $totalD += (float) $e->debit;
                $totalK += (float) $e->credit;
                fputcsv($handle, [
                    optional($e->journal->journal_date)->format('Y-m-d'),
                    $e->journal->journal_no,
                    $e->journal->reference,
                    $e->memo ?: $e->journal->description,
                    number_format((float)$e->debit, 2, '.', ''),
                    number_format((float)$e->credit, 2, '.', ''),
                ]);
            }
            // Totals
            fputcsv($handle, []);
            fputcsv($handle, ['TOTAL', '', '', '', number_format($totalD, 2, '.', ''), number_format($totalK, 2, '.', '')]);
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function exportGeneralLedgerPdf($entries, ?ChartOfAccount $account, string $from, string $to)
    {
        $pdf = Pdf::loadView('accounting.reports.general_ledger_pdf', [
            'entries' => $entries,
            'account' => $account,
            'from' => $from,
            'to' => $to,
        ])->setPaper('a4', 'portrait');

        $filename = 'general_ledger_'.$account->code.'_'.$from.'_to_'.$to.'.pdf';
        return $pdf->download($filename);
    }

    public function trialBalance(Request $request)
    {
        $periodId = $request->get('period_id');
        $periods = AccountingPeriod::orderByDesc('start_date')->get();

        $rows = collect();
        if ($periodId) {
            $rows = ChartOfAccount::select('chart_of_accounts.*')
                ->withSum(['journalDetails as debit_sum' => function($q) use ($periodId){
                    $q->whereHas('journal', fn($j)=>$j->where('accounting_period_id',$periodId));
                }], 'debit')
                ->withSum(['journalDetails as credit_sum' => function($q) use ($periodId){
                    $q->whereHas('journal', fn($j)=>$j->where('accounting_period_id',$periodId));
                }], 'credit')
                ->orderBy('code')
                ->get();
        }

        return view('accounting.reports.trial_balance', compact('periods','rows','periodId'));
    }

    public function incomeStatement(Request $request)
    {
        $periodId = $request->get('period_id');
        $periods = AccountingPeriod::orderByDesc('start_date')->get();
        $revenue = collect();
        $expense = collect();
        if ($periodId) {
            $revenue = ChartOfAccount::whereHas('accountType', fn($t)=>$t->where('name','Revenue'))
                ->withSum(['journalDetails as credit_sum' => function($q) use ($periodId){
                    $q->whereHas('journal', fn($j)=>$j->where('accounting_period_id',$periodId));
                }], 'credit')
                ->withSum(['journalDetails as debit_sum' => function($q) use ($periodId){
                    $q->whereHas('journal', fn($j)=>$j->where('accounting_period_id',$periodId));
                }], 'debit')
                ->orderBy('code')->get();

            $expense = ChartOfAccount::whereHas('accountType', fn($t)=>$t->where('name','Expense'))
                ->withSum(['journalDetails as debit_sum' => function($q) use ($periodId){
                    $q->whereHas('journal', fn($j)=>$j->where('accounting_period_id',$periodId));
                }], 'debit')
                ->withSum(['journalDetails as credit_sum' => function($q) use ($periodId){
                    $q->whereHas('journal', fn($j)=>$j->where('accounting_period_id',$periodId));
                }], 'credit')
                ->orderBy('code')->get();
        }

        return view('accounting.reports.income_statement', compact('periods','revenue','expense','periodId'));
    }

    public function balanceSheet(Request $request)
    {
        $periodId = $request->get('period_id');
        $periods = AccountingPeriod::orderByDesc('start_date')->get();
        $assets = collect();
        $liab = collect();
        $equity = collect();
        if ($periodId) {
            $assets = ChartOfAccount::whereHas('accountType', fn($t)=>$t->where('name','Asset'))
                ->withSum(['journalDetails as debit_sum' => function($q) use ($periodId){
                    $q->whereHas('journal', fn($j)=>$j->where('accounting_period_id',$periodId));
                }], 'debit')
                ->withSum(['journalDetails as credit_sum' => function($q) use ($periodId){
                    $q->whereHas('journal', fn($j)=>$j->where('accounting_period_id',$periodId));
                }], 'credit')
                ->orderBy('code')->get();

            $liab = ChartOfAccount::whereHas('accountType', fn($t)=>$t->where('name','Liability'))
                ->withSum(['journalDetails as debit_sum' => function($q) use ($periodId){
                    $q->whereHas('journal', fn($j)=>$j->where('accounting_period_id',$periodId));
                }], 'debit')
                ->withSum(['journalDetails as credit_sum' => function($q) use ($periodId){
                    $q->whereHas('journal', fn($j)=>$j->where('accounting_period_id',$periodId));
                }], 'credit')
                ->orderBy('code')->get();

            $equity = ChartOfAccount::whereHas('accountType', fn($t)=>$t->where('name','Equity'))
                ->withSum(['journalDetails as debit_sum' => function($q) use ($periodId){
                    $q->whereHas('journal', fn($j)=>$j->where('accounting_period_id',$periodId));
                }], 'debit')
                ->withSum(['journalDetails as credit_sum' => function($q) use ($periodId){
                    $q->whereHas('journal', fn($j)=>$j->where('accounting_period_id',$periodId));
                }], 'credit')
                ->orderBy('code')->get();
        }

        return view('accounting.reports.balance_sheet', compact('periods','assets','liab','equity','periodId'));
    }

    public function saveReport(Request $request)
    {
        $data = $request->validate([
            'report_name' => 'required|string|max:100',
            'accounting_period_id' => 'nullable|exists:accounting_periods,id',
            'snapshot' => 'required|array',
        ]);

        $history = ReportHistory::create([
            'report_name' => $data['report_name'],
            'accounting_period_id' => $data['accounting_period_id'] ?? null,
            'parameters' => $request->except(['_token','snapshot']),
            'snapshot' => $data['snapshot'],
            'generated_by' => optional($request->user())->name,
            'generated_at' => now(),
        ]);

        return response()->json(['success'=>true,'id'=>$history->id]);
    }
}
