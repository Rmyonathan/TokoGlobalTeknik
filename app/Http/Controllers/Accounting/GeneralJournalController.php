<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Journal;
use App\Models\JournalDetail;
use App\Models\ChartOfAccount;
use App\Models\AccountingPeriod;

class GeneralJournalController extends Controller
{
    public function index()
    {
        $journals = Journal::withCount('details')->orderByDesc('journal_date')->paginate(20);
        return view('accounting.general_journal.index', compact('journals'));
    }

    public function create()
    {
        $accounts = ChartOfAccount::where('is_active', true)->orderBy('code')->get();
        $periods = AccountingPeriod::orderByDesc('start_date')->get();
        return view('accounting.general_journal.create', compact('accounts','periods'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'journal_date' => 'required|date',
            'reference' => 'nullable|string|max:100',
            'description' => 'nullable|string|max:500',
            'accounting_period_id' => 'required|exists:accounting_periods,id',
            'lines' => 'required|array|min:1',
            'lines.*.account_id' => 'required|exists:chart_of_accounts,id',
            'lines.*.debit' => 'nullable|numeric|min:0',
            'lines.*.credit' => 'nullable|numeric|min:0',
            'lines.*.memo' => 'nullable|string|max:255',
        ]);

        $sumDebit = collect($data['lines'])->sum(function($l){ return (float)($l['debit'] ?? 0); });
        $sumCredit = collect($data['lines'])->sum(function($l){ return (float)($l['credit'] ?? 0); });

        if (round($sumDebit, 2) !== round($sumCredit, 2) || $sumDebit <= 0) {
            return back()->withInput()->withErrors(['lines' => 'Total debit dan kredit harus sama dan lebih dari 0.']);
        }

        try {
            DB::beginTransaction();

            $journal = Journal::create([
                'journal_no' => $this->generateJournalNo($data['journal_date']),
                'journal_date' => $data['journal_date'],
                'reference' => $data['reference'] ?? null,
                'description' => $data['description'] ?? null,
                'accounting_period_id' => $data['accounting_period_id'],
            ]);

            foreach ($data['lines'] as $line) {
                JournalDetail::create([
                    'journal_id' => $journal->id,
                    'account_id' => $line['account_id'],
                    'debit' => (float) ($line['debit'] ?? 0),
                    'credit' => (float) ($line['credit'] ?? 0),
                    'memo' => $line['memo'] ?? null,
                ]);
            }

            DB::commit();
            return redirect()->route('accounting.general-journal.index')->with('success', 'Jurnal berhasil disimpan.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error saving journal', ['message' => $e->getMessage()]);
            return back()->withInput()->with('error', 'Gagal menyimpan jurnal: '.$e->getMessage());
        }
    }

    public function show(Journal $journal)
    {
        // dd($journal);
        $journal->load(['details.account']);
        return view('accounting.general_journal.show', compact('journal'));
    }

    public function edit(Journal $journal)
    {
        $journal->load('details');
        // dd($journal);
        $accounts = ChartOfAccount::where('is_active', true)->orderBy('code')->get();
        $periods = AccountingPeriod::orderByDesc('start_date')->get();
        return view('accounting.general_journal.edit', compact('journal','accounts','periods'));
    }

    public function update(Request $request, Journal $journal)
    {
        $data = $request->validate([
            'journal_date' => 'required|date',
            'reference' => 'nullable|string|max:100',
            'description' => 'nullable|string|max:500',
            'accounting_period_id' => 'required|exists:accounting_periods,id',
            'lines' => 'required|array|min:1',
            'lines.*.id' => 'nullable|integer',
            'lines.*.account_id' => 'required|exists:chart_of_accounts,id',
            'lines.*.debit' => 'nullable|numeric|min:0',
            'lines.*.credit' => 'nullable|numeric|min:0',
            'lines.*.memo' => 'nullable|string|max:255',
        ]);

        $sumDebit = collect($data['lines'])->sum(function($l){ return (float)($l['debit'] ?? 0); });
        $sumCredit = collect($data['lines'])->sum(function($l){ return (float)($l['credit'] ?? 0); });
        if (round($sumDebit, 2) !== round($sumCredit, 2) || $sumDebit <= 0) {
            return back()->withInput()->withErrors(['lines' => 'Total debit dan kredit harus sama dan lebih dari 0.']);
        }

        try {
            DB::beginTransaction();

            $journal->update([
                'journal_date' => $data['journal_date'],
                'reference' => $data['reference'] ?? null,
                'description' => $data['description'] ?? null,
                'accounting_period_id' => $data['accounting_period_id'],
            ]);

            // Replace details for simplicity
            $journal->details()->delete();
            foreach ($data['lines'] as $line) {
                JournalDetail::create([
                    'journal_id' => $journal->id,
                    'account_id' => $line['account_id'],
                    'debit' => (float) ($line['debit'] ?? 0),
                    'credit' => (float) ($line['credit'] ?? 0),
                    'memo' => $line['memo'] ?? null,
                ]);
            }

            DB::commit();
            return redirect()->route('accounting.general-journal.index')->with('success', 'Jurnal berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating journal', ['message' => $e->getMessage()]);
            return back()->withInput()->with('error', 'Gagal memperbarui jurnal: '.$e->getMessage());
        }
    }

    public function destroy(Journal $journal)
    {
        $journal->details()->delete();
        $journal->delete();
        return redirect()->route('accounting.general-journal.index')->with('success', 'Jurnal dihapus.');
    }

    private function generateJournalNo(string $date): string
    {
        $prefix = 'GJ-'.date('Ymd', strtotime($date)).'-';
        $count = Journal::whereDate('journal_date', $date)->count() + 1;
        return $prefix.str_pad((string)$count, 3, '0', STR_PAD_LEFT);
    }
}
