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
        $entriesByAccount = collect();
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
        } else {
            // Load all entries within date range, grouped by account
            $entries = JournalDetail::with(['journal','account'])
                ->whereHas('journal', function($q) use ($from, $to){
                    $q->whereBetween('journal_date', [$from, $to]);
                })
                ->orderBy('account_id')
                ->orderBy('journal_id')
                ->orderBy('id')
                ->get();
            $entriesByAccount = $entries->groupBy('account_id');
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

        return view('accounting.reports.general_ledger', compact('accounts','entries','entriesByAccount','from','to','accountId','account'));
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
        $fromDate = $request->get('from_date');
        $toDate = $request->get('to_date');

        $rows = collect();
        if ($fromDate && $toDate) {
            $rows = ChartOfAccount::select('chart_of_accounts.*')
                ->withSum(['journalDetails as debit_sum' => function($q) use ($fromDate, $toDate){
                    $q->whereHas('journal', fn($j)=>$j->whereBetween('journal_date', [$fromDate, $toDate]));
                }], 'debit')
                ->withSum(['journalDetails as credit_sum' => function($q) use ($fromDate, $toDate){
                    $q->whereHas('journal', fn($j)=>$j->whereBetween('journal_date', [$fromDate, $toDate]));
                }], 'credit')
                ->orderBy('code')
                ->get();
        }

        return view('accounting.reports.trial_balance', compact('rows','fromDate','toDate'));
    }

    public function incomeStatement(Request $request)
    {
        $fromDate = $request->get('from_date');
        $toDate = $request->get('to_date');
        $revenue = collect();
        $expense = collect();
        
        if ($fromDate && $toDate) {
            $revenue = ChartOfAccount::whereHas('accountType', fn($t)=>$t->where('name','Revenue'))
                ->withSum(['journalDetails as credit_sum' => function($q) use ($fromDate, $toDate){
                    $q->whereHas('journal', fn($j)=>$j->whereBetween('journal_date', [$fromDate, $toDate]));
                }], 'credit')
                ->withSum(['journalDetails as debit_sum' => function($q) use ($fromDate, $toDate){
                    $q->whereHas('journal', fn($j)=>$j->whereBetween('journal_date', [$fromDate, $toDate]));
                }], 'debit')
                ->orderBy('code')->get();

            $expense = ChartOfAccount::whereHas('accountType', fn($t)=>$t->where('name','Expense'))
                ->withSum(['journalDetails as debit_sum' => function($q) use ($fromDate, $toDate){
                    $q->whereHas('journal', fn($j)=>$j->whereBetween('journal_date', [$fromDate, $toDate]));
                }], 'debit')
                ->withSum(['journalDetails as credit_sum' => function($q) use ($fromDate, $toDate){
                    $q->whereHas('journal', fn($j)=>$j->whereBetween('journal_date', [$fromDate, $toDate]));
                }], 'credit')
                ->orderBy('code')->get();
        }

        return view('accounting.reports.income_statement', compact('revenue','expense','fromDate','toDate'));
    }

    public function incomeStatementExport(Request $request)
    {
        $fromDate = $request->get('from_date');
        $toDate = $request->get('to_date');
        
        if (!$fromDate || !$toDate) {
            return redirect()->back()->with('error', 'Tanggal harus diisi');
        }
        
        $revenue = ChartOfAccount::whereHas('accountType', fn($t)=>$t->where('name','Revenue'))
            ->withSum(['journalDetails as credit_sum' => function($q) use ($fromDate, $toDate){
                $q->whereHas('journal', fn($j)=>$j->whereBetween('journal_date', [$fromDate, $toDate]));
            }], 'credit')
            ->withSum(['journalDetails as debit_sum' => function($q) use ($fromDate, $toDate){
                $q->whereHas('journal', fn($j)=>$j->whereBetween('journal_date', [$fromDate, $toDate]));
            }], 'debit')
            ->orderBy('code')->get();

        $expense = ChartOfAccount::whereHas('accountType', fn($t)=>$t->where('name','Expense'))
            ->withSum(['journalDetails as debit_sum' => function($q) use ($fromDate, $toDate){
                $q->whereHas('journal', fn($j)=>$j->whereBetween('journal_date', [$fromDate, $toDate]));
            }], 'debit')
            ->withSum(['journalDetails as credit_sum' => function($q) use ($fromDate, $toDate){
                $q->whereHas('journal', fn($j)=>$j->whereBetween('journal_date', [$fromDate, $toDate]));
            }], 'credit')
            ->orderBy('code')->get();

        $filename = 'laporan_laba_rugi_' . $fromDate . '_to_' . $toDate . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($revenue, $expense, $fromDate, $toDate) {
            $file = fopen('php://output', 'w');
            
            // BOM for UTF-8
            fwrite($file, "\xEF\xBB\xBF");
            
            // Header
            fputcsv($file, ['LAPORAN LABA RUGI']);
            fputcsv($file, ['Periode: ' . $fromDate . ' s/d ' . $toDate]);
            fputcsv($file, ['Tanggal Export: ' . now()->format('d-m-Y H:i:s')]);
            fputcsv($file, []);
            
            // Revenue Section
            fputcsv($file, ['PENDAPATAN']);
            fputcsv($file, ['Kode Akun', 'Nama Akun', 'Nilai']);
            
            $totalRevenue = 0;
            foreach ($revenue as $r) {
                $val = (float)($r->credit_sum ?? 0) - (float)($r->debit_sum ?? 0);
                fputcsv($file, [$r->code, $r->name, number_format($val, 2)]);
                $totalRevenue += $val;
            }
            fputcsv($file, ['', 'TOTAL PENDAPATAN', number_format($totalRevenue, 2)]);
            fputcsv($file, []);
            
            // Expense Section
            fputcsv($file, ['BEBAN']);
            fputcsv($file, ['Kode Akun', 'Nama Akun', 'Nilai']);
            
            $totalExpense = 0;
            foreach ($expense as $e) {
                $val = (float)($e->debit_sum ?? 0) - (float)($e->credit_sum ?? 0);
                fputcsv($file, [$e->code, $e->name, number_format($val, 2)]);
                $totalExpense += $val;
            }
            fputcsv($file, ['', 'TOTAL BEBAN', number_format($totalExpense, 2)]);
            fputcsv($file, []);
            
            // Net Income
            $netIncome = $totalRevenue - $totalExpense;
            fputcsv($file, ['', 'LABA/RUGI BERSIH', number_format($netIncome, 2)]);
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function balanceSheet(Request $request)
    {
        $fromDate = $request->get('from_date');
        $toDate = $request->get('to_date');
        $assets = collect();
        $liab = collect();
        $equity = collect();
        
        if ($fromDate && $toDate) {
            $assets = ChartOfAccount::whereHas('accountType', function($t){
                    $t->whereIn('name', ['Asset','Assets']);
                })
                ->withSum(['journalDetails as debit_sum' => function($q) use ($fromDate, $toDate){
                    $q->whereHas('journal', fn($j)=>$j->whereBetween('journal_date', [$fromDate, $toDate]));
                }], 'debit')
                ->withSum(['journalDetails as credit_sum' => function($q) use ($fromDate, $toDate){
                    $q->whereHas('journal', fn($j)=>$j->whereBetween('journal_date', [$fromDate, $toDate]));
                }], 'credit')
                ->orderBy('code')->get();

            $liab = ChartOfAccount::whereHas('accountType', function($t){
                    $t->whereIn('name', ['Liability','Liabilities']);
                })
                ->withSum(['journalDetails as debit_sum' => function($q) use ($fromDate, $toDate){
                    $q->whereHas('journal', fn($j)=>$j->whereBetween('journal_date', [$fromDate, $toDate]));
                }], 'debit')
                ->withSum(['journalDetails as credit_sum' => function($q) use ($fromDate, $toDate){
                    $q->whereHas('journal', fn($j)=>$j->whereBetween('journal_date', [$fromDate, $toDate]));
                }], 'credit')
                ->orderBy('code')->get();

            $equity = ChartOfAccount::whereHas('accountType', fn($t)=>$t->where('name','Equity'))
                ->withSum(['journalDetails as debit_sum' => function($q) use ($fromDate, $toDate){
                    $q->whereHas('journal', fn($j)=>$j->whereBetween('journal_date', [$fromDate, $toDate]));
                }], 'debit')
                ->withSum(['journalDetails as credit_sum' => function($q) use ($fromDate, $toDate){
                    $q->whereHas('journal', fn($j)=>$j->whereBetween('journal_date', [$fromDate, $toDate]));
                }], 'credit')
                ->orderBy('code')->get();
        }

        return view('accounting.reports.balance_sheet', compact('assets','liab','equity','fromDate','toDate'));
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
