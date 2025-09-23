<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ChartOfAccount;
use App\Models\Journal;
use App\Models\JournalDetail;
use App\Models\AccountType;
use Illuminate\Support\Facades\DB;
use Exception;

class BankLoanController extends Controller
{
    public function index()
    {
        // List of supported bank account names
        $banks = ['Bank BCA', 'Bank Mandiri', 'Bank BNI', 'Bank BRI'];
        return view('finance.bank_loan', compact('banks'));
    }

    public function disburse(Request $request)
    {
        $request->validate([
            'tanggal' => 'required|date',
            'bank' => 'required|string',
            'jumlah' => 'required|numeric|min:0.01',
            'keterangan' => 'nullable|string',
        ]);

        return $this->postDisbursement(
            $request->tanggal,
            trim($request->bank),
            (float) $request->jumlah,
            $request->keterangan ?? ''
        );
    }

    public function installment(Request $request)
    {
        $request->validate([
            'tanggal' => 'required|date',
            'bank' => 'required|string',
            'pokok' => 'required|numeric|min:0',
            'bunga' => 'required|numeric|min:0',
            'keterangan' => 'nullable|string',
        ]);

        return $this->postInstallment(
            $request->tanggal,
            trim($request->bank),
            (float) $request->pokok,
            (float) $request->bunga,
            $request->keterangan ?? ''
        );
    }

    private function postDisbursement(string $tanggal, string $bankName, float $amount, string $memo)
    {
        if ($amount <= 0) {
            return back()->withErrors(['error' => 'Jumlah tidak valid']);
        }

        DB::beginTransaction();
        try {
            $bank = $this->findAccountByName($this->normalizeBankName($bankName));
            $loan = $this->findOrCreateAccountByName('Utang Bank', 'Liability');

            if (!$bank || !$loan) throw new Exception('Akun Bank / Utang Bank tidak ditemukan');

            $jr = Journal::create([
                'journal_no' => $this->generateJournalNo('BANK-DSB'),
                'journal_date' => $tanggal,
                'description' => $memo ?: 'Pencairan Pinjaman Bank',
            ]);

            // Dr Bank
            JournalDetail::create([
                'journal_id' => $jr->id,
                'account_id' => $bank->id,
                'debit' => $amount,
                'credit' => 0,
                'memo' => 'Pencairan pinjaman '
            ]);

            // Cr Utang Bank
            JournalDetail::create([
                'journal_id' => $jr->id,
                'account_id' => $loan->id,
                'debit' => 0,
                'credit' => $amount,
                'memo' => 'Kewajiban pinjaman'
            ]);

            DB::commit();
            return back()->with('success', 'Pencairan pinjaman dicatat');
        } catch (Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    private function postInstallment(string $tanggal, string $bankName, float $pokok, float $bunga, string $memo)
    {
        if ($pokok < 0 || $bunga < 0 || ($pokok + $bunga) <= 0) {
            return back()->withErrors(['error' => 'Nilai angsuran tidak valid']);
        }

        DB::beginTransaction();
        try {
            $bank = $this->findAccountByName($this->normalizeBankName($bankName));
            $loan = $this->findOrCreateAccountByName('Utang Bank', 'Liability');
            $interestExp = $this->findOrCreateAccountByName('Beban Bunga', 'Expense');

            if (!$bank || !$loan || !$interestExp) throw new Exception('Akun Bank / Utang Bank / Beban Bunga tidak ditemukan');

            $jr = Journal::create([
                'journal_no' => $this->generateJournalNo('BANK-ANGS'),
                'journal_date' => $tanggal,
                'description' => $memo ?: 'Angsuran Pinjaman Bank',
            ]);

            // Dr Utang Bank (pokok)
            if ($pokok > 0) {
                JournalDetail::create([
                    'journal_id' => $jr->id,
                    'account_id' => $loan->id,
                    'debit' => $pokok,
                    'credit' => 0,
                    'memo' => 'Pelunasan pokok pinjaman'
                ]);
            }

            // Dr Beban Bunga (bunga)
            if ($bunga > 0) {
                JournalDetail::create([
                    'journal_id' => $jr->id,
                    'account_id' => $interestExp->id,
                    'debit' => $bunga,
                    'credit' => 0,
                    'memo' => 'Beban bunga pinjaman'
                ]);
            }

            // Cr Bank (total dibayar)
            $total = $pokok + $bunga;
            JournalDetail::create([
                'journal_id' => $jr->id,
                'account_id' => $bank->id,
                'debit' => 0,
                'credit' => $total,
                'memo' => 'Pembayaran angsuran'
            ]);

            DB::commit();
            return back()->with('success', 'Angsuran pinjaman dicatat');
        } catch (Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    private function findAccountByName(string $name): ?ChartOfAccount
    {
        return ChartOfAccount::whereRaw('LOWER(name)=?', [mb_strtolower(trim($name))])->first();
    }

    private function findOrCreateAccountByName(string $name, string $type): ?ChartOfAccount
    {
        $acc = $this->findAccountByName($name);
        if ($acc) return $acc;

        $typeId = $this->resolveAccountTypeId($type);
        if (!$typeId) {
            // fallback: create AccountType if missing
            $map = $this->accountTypeMap();
            $meta = $map[strtolower($type)] ?? ['code' => 'X', 'name' => 'Expense'];
            $typeModel = AccountType::firstOrCreate(
                ['code' => $meta['code']],
                ['name' => $meta['name'], 'normal_balance' => ($meta['code'] === 'A' || $meta['code'] === 'X') ? 'D' : 'C']
            );
            $typeId = $typeModel->id;
        }

        return ChartOfAccount::create([
            'code' => $this->generateCodeFor($name),
            'name' => $name,
            'account_type_id' => $typeId,
            'is_active' => true,
        ]);
    }

    private function normalizeBankName(string $label): string
    {
        $map = [
            'BCA' => 'Bank BCA',
            'Mandiri' => 'Bank Mandiri',
            'BNI' => 'Bank BNI',
            'BRI' => 'Bank BRI',
        ];
        return $map[$label] ?? $label;
    }

    private function generateJournalNo(string $prefix): string
    {
        return $prefix.'-'.now()->format('Ymd-His');
    }

    private function generateCodeFor(string $name): string
    {
        // Simple code generator based on initials
        $slug = strtoupper(preg_replace('/[^A-Z0-9]+/i', '', $name));
        $base = substr($slug, 0, 4);
        $seq = str_pad((string) rand(1, 999), 3, '0', STR_PAD_LEFT);
        return $base.'-'.$seq;
    }

    private function resolveAccountTypeId(string $typeLabel): ?int
    {
        $map = $this->accountTypeMap();
        $key = strtolower(trim($typeLabel));
        $meta = $map[$key] ?? null;
        if ($meta) {
            $type = AccountType::where('code', $meta['code'])->first();
            if ($type) return $type->id;
            $type = AccountType::whereRaw('LOWER(name)=?', [mb_strtolower($meta['name'])])->first();
            if ($type) return $type->id;
        }
        return null;
    }

    private function accountTypeMap(): array
    {
        return [
            'asset' => ['code' => 'A', 'name' => 'Assets'],
            'assets' => ['code' => 'A', 'name' => 'Assets'],
            'liability' => ['code' => 'L', 'name' => 'Liabilities'],
            'liabilities' => ['code' => 'L', 'name' => 'Liabilities'],
            'equity' => ['code' => 'E', 'name' => 'Equity'],
            'revenue' => ['code' => 'R', 'name' => 'Revenue'],
            'income' => ['code' => 'R', 'name' => 'Revenue'],
            'expense' => ['code' => 'X', 'name' => 'Expense'],
            'expenses' => ['code' => 'X', 'name' => 'Expense'],
        ];
    }
}


