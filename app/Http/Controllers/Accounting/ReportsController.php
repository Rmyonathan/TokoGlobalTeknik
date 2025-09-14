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

class ReportsController extends Controller
{
    public function generalLedger(Request $request)
    {
        $periodId = $request->get('period_id');
        $accountId = $request->get('account_id');
        $periods = AccountingPeriod::orderByDesc('start_date')->get();
        $accounts = ChartOfAccount::orderBy('code')->get();

        $entries = collect();
        if ($periodId && $accountId) {
            $entries = JournalDetail::with(['journal'])
                ->where('account_id', $accountId)
                ->whereHas('journal', function($q) use ($periodId){
                    $q->where('accounting_period_id', $periodId);
                })
                ->orderByDesc('id')
                ->get();
        }

        return view('accounting.reports.general_ledger', compact('periods','accounts','entries','periodId','accountId'));
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
