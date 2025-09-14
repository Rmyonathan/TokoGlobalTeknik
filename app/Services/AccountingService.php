<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Journal;
use App\Models\JournalDetail;
use App\Models\AccountingPeriod;
use App\Models\ChartOfAccount;

class AccountingService
{
    public function createJournal(string $date, string $reference, string $description, array $lines, ?int $periodId = null): ?Journal
    {
        $sumDebit = collect($lines)->sum(fn($l)=>(float)($l['debit'] ?? 0));
        $sumCredit = collect($lines)->sum(fn($l)=>(float)($l['credit'] ?? 0));
        if (round($sumDebit,2) !== round($sumCredit,2) || $sumDebit <= 0) {
            Log::warning('Journal not balanced', ['reference'=>$reference,'debit'=>$sumDebit,'credit'=>$sumCredit]);
            return null;
        }

        $periodId = $periodId ?: optional(AccountingPeriod::where('is_closed', false)->orderByDesc('start_date')->first())->id;

        return DB::transaction(function() use ($date,$reference,$description,$lines,$periodId){
            $journal = Journal::create([
                'journal_no' => $this->generateJournalNo($date),
                'journal_date' => $date,
                'reference' => $reference,
                'description' => $description,
                'accounting_period_id' => $periodId,
            ]);
            
            $affectedAccountIds = [];
            foreach ($lines as $l) {
                JournalDetail::create([
                    'journal_id' => $journal->id,
                    'account_id' => $l['account_id'],
                    'debit' => (float)($l['debit'] ?? 0),
                    'credit' => (float)($l['credit'] ?? 0),
                    'memo' => $l['memo'] ?? null,
                ]);
                $affectedAccountIds[] = $l['account_id'];
            }
            
            // Update balances for affected accounts
            $this->updateAccountBalances($affectedAccountIds, $periodId);
            
            return $journal;
        });
    }

    // Sales Invoice: Dr Accounts Receivable, Cr Sales Revenue, Cr VAT Payable
    public function createJournalFromSale($transaksi): ?Journal
    {
        $date = optional($transaksi->tanggal)->format('Y-m-d') ?: now()->format('Y-m-d');
        $reference = $transaksi->no_transaksi ?? 'SALE';
        $desc = 'Jurnal Penjualan '.$reference;

        $piutang = $this->findAccountAny(['Piutang Usaha']);
        $pendapatan = $this->findAccountAny(['Pendapatan Penjualan']);
        $utangPpn = $this->findAccountAny(['Utang PPN', 'Utang PPN (PPN Keluaran)']);
        $hpp = $this->findAccountAny(['Harga Pokok Penjualan (HPP)', 'Harga Pokok Penjualan']);
        $persediaan = $this->findAccountAny(['Persediaan Barang Dagang', 'Persediaan']);

        if (!$piutang || !$pendapatan) {
            Log::warning('COA not found for sales journal', compact('piutang','pendapatan','utangPpn'));
            return null;
        }

        $ppn = (float)($transaksi->ppn_total ?? 0);
        $grand = (float)($transaksi->grand_total ?? 0);
        $sales = max(0.0, $grand - $ppn);

        $lines = [
            ['account_id'=>$piutang->id,'debit'=>$grand,'credit'=>0,'memo'=>'Piutang usaha penjualan'],
            ['account_id'=>$pendapatan->id,'debit'=>0,'credit'=>$sales,'memo'=>'Pendapatan penjualan'],
        ];
        if ($ppn > 0 && $utangPpn) {
            $lines[] = ['account_id'=>$utangPpn->id,'debit'=>0,'credit'=>$ppn,'memo'=>'PPN Keluaran'];
        }
        // Tambahkan jurnal HPP jika data modal tersedia dan akun ditemukan
        if ($hpp && $persediaan) {
            try {
                $modal = 0.0;
                $transaksi->loadMissing('items.transaksiItemSumber.stockBatch');
                foreach (($transaksi->items ?? []) as $item) {
                    foreach (($item->transaksiItemSumber ?? []) as $sumber) {
                        if ($sumber->stockBatch) {
                            $modal += ((float)$sumber->qty_diambil) * ((float)$sumber->stockBatch->harga_beli);
                        }
                    }
                }
                if ($modal > 0) {
                    $lines[] = ['account_id'=>$hpp->id,'debit'=>$modal,'credit'=>0,'memo'=>'HPP'];
                    $lines[] = ['account_id'=>$persediaan->id,'debit'=>0,'credit'=>$modal,'memo'=>'Pengurangan persediaan (HPP)'];
                }
            } catch (\Exception $e) {
                Log::warning('Failed to compute HPP for sales journal', ['message'=>$e->getMessage(), 'ref'=>$reference]);
            }
        }
        return $this->createJournal($date, $reference, $desc, $lines);
    }

    // Purchase: Dr Inventory, Dr VAT Receivable, Cr Cash/AP
    public function createJournalFromPurchase($pembelian): ?Journal
    {
        $date = optional($pembelian->tanggal)->format('Y-m-d') ?: now()->format('Y-m-d');
        $reference = $pembelian->nota ?? 'PURCHASE';
        $desc = 'Jurnal Pembelian '.$reference;

        $persediaan = $this->findAccountAny(['Persediaan Barang Dagang', 'Persediaan']);
        $piutangPpn = $this->findAccountAny(['Piutang PPN', 'Piutang PPN (PPN Masukan)']);
        $utangUsaha = $this->findAccountAny(['Utang Usaha']);
        $kas = $this->findAccountAny(['Kas']);

        if (!$persediaan) { Log::warning('COA Persediaan not found'); return null; }

        $ppn = (float)($pembelian->ppn_total ?? 0);
        $grand = (float)($pembelian->grand_total ?? 0);
        $net = max(0.0, $grand - $ppn);

        $lines = [
            ['account_id'=>$persediaan->id,'debit'=>$net,'credit'=>0,'memo'=>'Persediaan dari pembelian'],
        ];
        if ($ppn > 0 && $piutangPpn) {
            $lines[] = ['account_id'=>$piutangPpn->id,'debit'=>$ppn,'credit'=>0,'memo'=>'PPN Masukan'];
        }

        // If paid -> cash; else AP
        $isCredit = in_array($pembelian->cara_bayar ?? '', ['tempo','kredit','utang']);
        $creditAccount = $isCredit && $utangUsaha ? $utangUsaha : ($kas ?: $utangUsaha);
        if (!$creditAccount) { Log::warning('COA Kas/Utang Usaha not found'); return null; }
        $lines[] = ['account_id'=>$creditAccount->id,'debit'=>0,'credit'=>$grand,'memo'=>'Kredit pembelian'];

        return $this->createJournal($date, $reference, $desc, $lines);
    }

    // AR Payment: Dr Cash/Bank, Cr Accounts Receivable
    public function createJournalFromPaymentAR($payment): ?Journal
    {
        $date = optional($payment->tanggal)->format('Y-m-d') ?: now()->format('Y-m-d');
        $reference = $payment->no_pembayaran ?? 'PAY-AR';
        $desc = 'Pembayaran Piutang '.$reference;

        $kas = $this->findAccount('Kas') ?: $this->findAccount('Bank');
        $piutang = $this->findAccount('Piutang Usaha');
        if (!$kas || !$piutang) { Log::warning('COA Kas/Bank or Piutang not found'); return null; }
        $amount = (float)($payment->total_dibayar ?? $payment->jumlah ?? 0);
        $lines = [
            ['account_id'=>$kas->id,'debit'=>$amount,'credit'=>0,'memo'=>'Terima pembayaran pelanggan'],
            ['account_id'=>$piutang->id,'debit'=>0,'credit'=>$amount,'memo'=>'Pelunasan piutang'],
        ];
        return $this->createJournal($date, $reference, $desc, $lines);
    }

    // AP Payment: Dr Accounts Payable, Cr Cash/Bank
    public function createJournalFromPaymentAP($payment): ?Journal
    {
        $date = optional($payment->tanggal)->format('Y-m-d') ?: now()->format('Y-m-d');
        $reference = $payment->no_pembayaran ?? 'PAY-AP';
        $desc = 'Pembayaran Utang '.$reference;

        $utang = $this->findAccountAny(['Utang Usaha']);
        $kas = $this->findAccountAny(['Kas','Bank']);
        if (!$utang || !$kas) { Log::warning('COA Utang/Kas not found'); return null; }
        $amount = (float)($payment->total_dibayar ?? $payment->jumlah ?? 0);
        $lines = [
            ['account_id'=>$utang->id,'debit'=>$amount,'credit'=>0,'memo'=>'Pelunasan utang'],
            ['account_id'=>$kas->id,'debit'=>0,'credit'=>$amount,'memo'=>'Pembayaran kepada supplier'],
        ];
        return $this->createJournal($date, $reference, $desc, $lines);
    }

    // Sales Return: Dr Sales Return, Cr Accounts Receivable
    public function createJournalFromSalesReturn($retur): ?Journal
    {
        $date = optional($retur->tanggal)->format('Y-m-d') ?: now()->format('Y-m-d');
        $reference = $retur->no_retur ?? 'RET-SALES';
        $desc = 'Retur Penjualan '.$reference;
        $returPenjualan = $this->findAccountAny(['Retur Penjualan']);
        $piutang = $this->findAccountAny(['Piutang Usaha']);
        if (!$returPenjualan || !$piutang) { Log::warning('COA Retur Penjualan/Piutang not found'); return null; }
        $amount = (float)($retur->total_retur ?? 0);
        $lines = [
            ['account_id'=>$returPenjualan->id,'debit'=>$amount,'credit'=>0,'memo'=>'Retur penjualan'],
            ['account_id'=>$piutang->id,'debit'=>0,'credit'=>$amount,'memo'=>'Koreksi piutang'],
        ];
        return $this->createJournal($date, $reference, $desc, $lines);
    }

    // Purchase Return: Dr Accounts Payable, Cr Inventory
    public function createJournalFromPurchaseReturn($retur): ?Journal
    {
        $date = optional($retur->tanggal)->format('Y-m-d') ?: now()->format('Y-m-d');
        $reference = $retur->no_retur ?? 'RET-PURCHASE';
        $desc = 'Retur Pembelian '.$reference;
        $utang = $this->findAccountAny(['Utang Usaha']);
        $persediaan = $this->findAccountAny(['Persediaan Barang Dagang','Persediaan']);
        if (!$utang || !$persediaan) { Log::warning('COA Utang/Persediaan not found'); return null; }
        $amount = (float)($retur->total_retur ?? 0);
        $lines = [
            ['account_id'=>$utang->id,'debit'=>$amount,'credit'=>0,'memo'=>'Koreksi utang'],
            ['account_id'=>$persediaan->id,'debit'=>0,'credit'=>$amount,'memo'=>'Retur pembelian - pengurangan persediaan'],
        ];
        return $this->createJournal($date, $reference, $desc, $lines);
    }

    private function findAccount(string $accountName): ?ChartOfAccount
    {
        return ChartOfAccount::where('name', $accountName)->first();
    }

    private function findAccountAny(array $candidates): ?ChartOfAccount
    {
        foreach ($candidates as $name) {
            $acc = $this->findAccount($name);
            if ($acc) return $acc;
        }
        // fallback like-search
        foreach ($candidates as $name) {
            $acc = ChartOfAccount::where('name', 'like', "%{$name}%")->first();
            if ($acc) return $acc;
        }
        return null;
    }

    private function generateJournalNo(string $date): string
    {
        $prefix = 'AUTO-'.date('Ymd', strtotime($date)).'-';
        $count = Journal::whereDate('journal_date', $date)->count() + 1;
        return $prefix.str_pad((string)$count, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Update balances for multiple accounts
     */
    private function updateAccountBalances(array $accountIds, ?int $periodId = null): void
    {
        $uniqueAccountIds = array_unique($accountIds);
        
        foreach ($uniqueAccountIds as $accountId) {
            $account = ChartOfAccount::find($accountId);
            if ($account) {
                $account->updateBalance($periodId);
            }
        }
    }

    /**
     * Recalculate all account balances
     */
    public function recalculateAllBalances(?int $periodId = null): int
    {
        $accounts = ChartOfAccount::where('is_active', true)->get();
        $updatedCount = 0;
        
        foreach ($accounts as $account) {
            if ($account->updateBalance($periodId)) {
                $updatedCount++;
            }
        }
        
        return $updatedCount;
    }

    /**
     * Get accounts by type with balances
     */
    public function getAccountsByType(string $accountTypeName, ?int $periodId = null): \Illuminate\Database\Eloquent\Collection
    {
        return ChartOfAccount::whereHas('accountType', function($q) use ($accountTypeName) {
                $q->where('name', $accountTypeName);
            })
            ->where('is_active', true)
            ->with('accountType')
            ->get()
            ->map(function($account) use ($periodId) {
                $account->current_balance = $account->calculateBalance($periodId);
                return $account;
            });
    }

    /**
     * Get total balance by account type
     */
    public function getTotalBalanceByType(string $accountTypeName, ?int $periodId = null): float
    {
        $accounts = $this->getAccountsByType($accountTypeName, $periodId);
        return $accounts->sum('current_balance');
    }
}


