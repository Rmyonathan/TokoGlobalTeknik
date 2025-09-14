<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\AccountingPeriod;
use App\Models\YearEndClosing;
use App\Models\ReportHistory;

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
            $closing = YearEndClosing::create([
                'accounting_period_id' => $data['accounting_period_id'],
                'fiscal_year' => $data['fiscal_year'],
                'status' => 'closed',
                'closed_on' => now(),
                'closed_by' => optional($request->user())->name,
                'metadata' => [
                    'note' => 'Auto closing created',
                ],
                'snapshots' => [
                    'reports' => ReportHistory::where('accounting_period_id', $data['accounting_period_id'])->get()->toArray(),
                ]
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
}
