<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\AccountingPeriod;
use App\Models\YearEndClosing;
use App\Models\ReportHistory;
use App\Models\Journal;
use App\Models\JournalDetail;
use App\Models\ChartOfAccount;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Barryvdh\DomPDF\Facade\Pdf;

class YearEndClosingController extends Controller
{
    public function index()
    {
        $closings = YearEndClosing::orderByDesc('fiscal_year')->paginate(20);
        return view('accounting.year_end.index', compact('closings'));
    }

    public function create()
    {
        $periods = AccountingPeriod::orderByDesc('start_date')->get();
        return view('accounting.year_end.create', compact('periods'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'accounting_period_id' => 'required|exists:accounting_periods,id',
            'fiscal_year' => 'required|digits:4',
        ]);

        DB::beginTransaction();
        try {
            $period = AccountingPeriod::findOrFail($data['accounting_period_id']);

            // Build snapshot summary for the closing period
            $journalCount = Journal::where('accounting_period_id', $period->id)->count();
            $totals = JournalDetail::selectRaw('SUM(debit) as total_debit, SUM(credit) as total_credit')
                ->whereHas('journal', function($q) use ($period){
                    $q->where('accounting_period_id', $period->id);
                })
                ->first();

            // Top accounts by movement during the period
            $topAccounts = JournalDetail::selectRaw('account_id, SUM(debit) as debit, SUM(credit) as credit')
                ->whereHas('journal', function($q) use ($period){
                    $q->where('accounting_period_id', $period->id);
                })
                ->groupBy('account_id')
                ->orderByRaw('GREATEST(SUM(debit), SUM(credit)) DESC')
                ->limit(10)
                ->get()
                ->map(function($row){
                    $acc = ChartOfAccount::find($row->account_id);
                    return [
                        'code' => $acc->code ?? '-',
                        'name' => $acc->name ?? '-',
                        'debit' => (float) $row->debit,
                        'credit' => (float) $row->credit,
                    ];
                })->toArray();

            $snapshots = [
                'summary' => [
                    'period' => [
                        'name' => $period->name ?? ($period->start_date.' - '.$period->end_date),
                        'start_date' => optional($period->start_date)->format('Y-m-d'),
                        'end_date' => optional($period->end_date)->format('Y-m-d'),
                    ],
                    'journal_count' => $journalCount,
                    'total_debit' => (float) ($totals->total_debit ?? 0),
                    'total_credit' => (float) ($totals->total_credit ?? 0),
                ],
                'top_accounts' => $topAccounts,
                'reports' => ReportHistory::where('accounting_period_id', $period->id)->get()->toArray(),
            ];

            $closing = YearEndClosing::create([
                'accounting_period_id' => $period->id,
                'fiscal_year' => $data['fiscal_year'],
                'status' => 'closed',
                'closed_on' => now(),
                'closed_by' => optional($request->user())->name,
                'metadata' => [ 'note' => 'Auto closing created' ],
                'snapshots' => $snapshots,
            ]);

            DB::commit();
            return redirect()->route('accounting.year-end.index')->with('success','Tutup buku berhasil.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error','Gagal tutup buku: '.$e->getMessage());
        }
    }

    public function show(YearEndClosing $yearEnd)
    {
        return view('accounting.year_end.show', ['closing' => $yearEnd]);
    }

    public function exportPdf(YearEndClosing $yearEnd)
    {
        $pdf = Pdf::loadView('accounting.year_end.pdf', [
            'closing' => $yearEnd,
        ])->setPaper('a4', 'portrait');
        $filename = 'year_end_'.$yearEnd->fiscal_year.'.pdf';
        return $pdf->download($filename);
    }

    public function exportCsv(YearEndClosing $yearEnd): StreamedResponse
    {
        $filename = 'year_end_'.$yearEnd->fiscal_year.'.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ];
        $snap = $yearEnd->snapshots ?? [];
        $callback = function() use ($snap, $yearEnd) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Tutup Buku', $yearEnd->fiscal_year]);
            fputcsv($out, ['Tanggal Tutup', optional($yearEnd->closed_on)->format('Y-m-d')]);
            fputcsv($out, ['Ditutup Oleh', $yearEnd->closed_by]);
            fputcsv($out, []);
            // Summary
            if (!empty($snap['summary'])) {
                fputcsv($out, ['Ringkasan']);
                fputcsv($out, ['Periode', ($snap['summary']['period']['start_date'] ?? '-').' s/d '.($snap['summary']['period']['end_date'] ?? '-')]);
                fputcsv($out, ['Jumlah Jurnal', $snap['summary']['journal_count'] ?? 0]);
                fputcsv($out, ['Total Debit', $snap['summary']['total_debit'] ?? 0]);
                fputcsv($out, ['Total Kredit', $snap['summary']['total_credit'] ?? 0]);
                fputcsv($out, []);
            }
            // Top accounts
            if (!empty($snap['top_accounts'])) {
                fputcsv($out, ['Top Akun', 'Kode', 'Nama', 'Debet', 'Kredit']);
                foreach ($snap['top_accounts'] as $row) {
                    fputcsv($out, ['', $row['code'] ?? '-', $row['name'] ?? '-', $row['debit'] ?? 0, $row['credit'] ?? 0]);
                }
                fputcsv($out, []);
            }
            // Reports
            fputcsv($out, ['Reports']);
            fputcsv($out, ['Jenis', 'Nama', 'Dibuat Pada', 'Metadata']);
            foreach (($snap['reports'] ?? []) as $report) {
                fputcsv($out, [
                    $report['type'] ?? '-',
                    $report['name'] ?? '-',
                    isset($report['created_at']) ? (string)$report['created_at'] : '-',
                    isset($report['metadata']) ? json_encode($report['metadata']) : '-',
                ]);
            }
            fclose($out);
        };
        return response()->stream($callback, 200, $headers);
    }

    public function exportAllAccountsCsv(YearEndClosing $yearEnd): StreamedResponse
    {
        $periodId = $yearEnd->accounting_period_id;
        $filename = 'year_end_'.$yearEnd->fiscal_year.'_all_accounts.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ];

        $rows = JournalDetail::selectRaw('account_id, SUM(debit) as debit, SUM(credit) as credit')
            ->whereHas('journal', function($q) use ($periodId){
                $q->where('accounting_period_id', $periodId);
            })
            ->groupBy('account_id')
            ->orderByRaw('code asc')
            ->get()
            ->map(function($r){
                $acc = ChartOfAccount::find($r->account_id);
                return [
                    'code' => $acc->code ?? '-',
                    'name' => $acc->name ?? '-',
                    'debit' => (float) $r->debit,
                    'credit' => (float) $r->credit,
                ];
            });

        $callback = function() use ($rows, $yearEnd) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Tutup Buku (Semua Akun)', $yearEnd->fiscal_year]);
            fputcsv($out, ['Kode', 'Nama Akun', 'Debet', 'Kredit']);
            foreach ($rows as $row) {
                fputcsv($out, [$row['code'], $row['name'], $row['debit'], $row['credit']]);
            }
            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }
}
