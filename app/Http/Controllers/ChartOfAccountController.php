<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AccountType;
use App\Models\ChartOfAccount;
use App\Services\AccountingService;
use Illuminate\Http\Request;

class ChartOfAccountController extends Controller
{
    protected $accountingService;

    public function __construct(AccountingService $accountingService)
    {
        $this->accountingService = $accountingService;
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $accounts = ChartOfAccount::with(['type', 'parent'])->orderBy('code')->paginate(20);
        $accountTypes = AccountType::orderBy('name')->get();
        $parents = ChartOfAccount::orderBy('code')->get();
        return view('chart_of_accounts.index', compact('accounts', 'accountTypes', 'parents'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $account = new ChartOfAccount();
        $accountTypes = AccountType::orderBy('name')->get();
        $parents = ChartOfAccount::orderBy('code')->get();
        return view('chart_of_accounts.create', compact('account', 'accountTypes', 'parents'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:255|unique:chart_of_accounts,code',
            'name' => 'required|string|max:255',
            'account_type_id' => 'required|exists:account_types,id',
            'parent_id' => 'nullable|exists:chart_of_accounts,id',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        $account = ChartOfAccount::create($validated);

        return redirect()->route('chart-of-accounts.index')->with('success', 'Account created');
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, ChartOfAccount $chart_of_account)
    {
        $account = $chart_of_account->load(['type', 'parent', 'children']);
        $jdQuery = $chart_of_account->journalDetails()->with('journal');

        // Optional date filtering by journal_date
        $start = $request->query('tanggal_awal');
        $end   = $request->query('tanggal_akhir');
        if ($start) {
            $jdQuery->whereHas('journal', function($q) use ($start) {
                $q->whereDate('journal_date', '>=', $start);
            });
        }
        if ($end) {
            $jdQuery->whereHas('journal', function($q) use ($end) {
                $q->whereDate('journal_date', '<=', $end);
            });
        }

        $journalDetails = $jdQuery->orderByDesc('id')->paginate(20)->appends($request->only(['tanggal_awal','tanggal_akhir']));

        return view('chart_of_accounts.show', compact('account', 'journalDetails', 'start', 'end'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ChartOfAccount $chart_of_account)
    {
        $accountTypes = AccountType::orderBy('name')->get();
        $parents = ChartOfAccount::where('id', '!=', $chart_of_account->id)->orderBy('code')->get();
        return view('chart_of_accounts.edit', ['account' => $chart_of_account, 'accountTypes' => $accountTypes, 'parents' => $parents]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ChartOfAccount $chart_of_account)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:255|unique:chart_of_accounts,code,' . $chart_of_account->id,
            'name' => 'required|string|max:255',
            'account_type_id' => 'required|exists:account_types,id',
            'parent_id' => 'nullable|exists:chart_of_accounts,id|not_in:' . $chart_of_account->id,
            'is_active' => 'nullable|boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        $chart_of_account->update($validated);

        return redirect()->route('chart-of-accounts.index')->with('success', 'Account updated');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ChartOfAccount $chart_of_account)
    {
        $chart_of_account->delete();
        return redirect()->route('chart-of-accounts.index')->with('success', 'Account deleted');
    }

    /**
     * Recalculate all account balances
     */
    public function recalculateBalances()
    {
        try {
            $updatedCount = $this->accountingService->recalculateAllBalances();
            return response()->json([
                'success' => true,
                'message' => "Berhasil menghitung ulang {$updatedCount} akun",
                'updated_count' => $updatedCount
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghitung ulang saldo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Recalculate balance for a specific account
     */
    public function recalculateBalance(ChartOfAccount $chart_of_account)
    {
        try {
            $success = $chart_of_account->updateBalance();
            if ($success) {
                return response()->json([
                    'success' => true,
                    'message' => "Berhasil menghitung ulang saldo akun {$chart_of_account->name}",
                    'balance' => $chart_of_account->fresh()->balance
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menghitung ulang saldo akun'
                ], 500);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghitung ulang saldo: ' . $e->getMessage()
            ], 500);
        }
    }
}
