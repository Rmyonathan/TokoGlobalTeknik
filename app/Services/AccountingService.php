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

    // Sales Invoice:
    // - Tunai: Dr Cash/Bank; Kredit: Dr Accounts Receivable
    // - Cr Sales Revenue (DPP)
    // - Cr VAT Payable (if PPN active/exists on transaction)
    // - Dr COGS; Cr Inventory for FIFO-based cost
    public function createJournalFromSale($transaksi): ?Journal
    {
        $date = optional($transaksi->tanggal)->format('Y-m-d') ?: now()->format('Y-m-d');
        $reference = $transaksi->no_transaksi ?? 'SALE';
        $desc = 'Jurnal Penjualan '.$reference;

        $piutang = $this->findAccountAny(['Piutang Usaha']);
        $kasOrBank = $this->findAccountAny(['Kas','Bank','Kas Besar','Kas Kecil']);
        $pendapatan = $this->findAccountAny(['Penjualan','Pendapatan Penjualan']);
        $utangPpn = $this->findAccountAny(['PPN Keluaran','Utang PPN', 'Utang PPN (PPN Keluaran)']);
        $hpp = $this->findAccountAny(['COGS','Harga Pokok Penjualan (HPP)', 'Harga Pokok Penjualan']);
        $persediaan = $this->findAccountAny(['Persediaan','Persediaan Barang Dagang']);

        if ((!$piutang && !$kasOrBank) || !$pendapatan) {
            Log::warning('COA not found for sales journal', compact('piutang','kasOrBank','pendapatan','utangPpn'));
            return null;
        }

        // Use transaction PPN (already calculated considering DB2 enablement)
        $ppn = (float)($transaksi->ppn ?? 0);
        $grand = (float)($transaksi->grand_total ?? 0);
        $sales = max(0.0, $grand - $ppn);

        // Decide cash vs credit
        $isCash = false;
        $caraBayar = strtolower((string)($transaksi->cara_bayar ?? ''));
        $pembayaran = strtolower((string)($transaksi->pembayaran ?? ''));
        if (in_array($caraBayar, ['tunai','cash']) || in_array($pembayaran, ['tunai','cash'])) {
            $isCash = true;
        }

        $lines = [];
        if ($isCash && $kasOrBank) {
            $lines[] = ['account_id'=>$kasOrBank->id,'debit'=>$grand,'credit'=>0,'memo'=>'Penerimaan penjualan tunai'];
        } else {
            // default to AR
            $lines[] = ['account_id'=>$piutang->id,'debit'=>$grand,'credit'=>0,'memo'=>'Piutang usaha penjualan'];
        }
        $lines[] = ['account_id'=>$pendapatan->id,'debit'=>0,'credit'=>$sales,'memo'=>'Pendapatan penjualan'];
        if ($ppn > 0 && $utangPpn) {
            $lines[] = ['account_id'=>$utangPpn->id,'debit'=>0,'credit'=>$ppn,'memo'=>'PPN Keluaran'];
        }
        // Tambahkan jurnal HPP jika data modal tersedia dan akun ditemukan
        if ($hpp && $persediaan) {
            try {
                $modal = 0.0;
                if (method_exists($transaksi, 'loadMissing')) {
                    $transaksi->loadMissing('items.transaksiItemSumber.stockBatch');
                }
                foreach (($transaksi->items ?? []) as $item) {
                    $sumbers = $item->transaksiItemSumber ?? ($item->sumber ?? []);
                    foreach ($sumbers as $sumber) {
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

        $persediaan = $this->findAccountAny(['Persediaan','Persediaan Barang Dagang']);
        $piutangPpn = $this->findAccountAny(['PPN Masukan','Piutang PPN', 'Piutang PPN (PPN Masukan)']);
        $utangUsaha = $this->findAccountAny(['Utang Usaha']);
        $kas = $this->findAccountAny(['Kas','Bank','Kas Besar','Kas Kecil']);

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
        // FIX: Perbaikan logika jurnal - Non Tunai = Credit, Tunai = Cash
        $isCredit = ($pembelian->pembayaran ?? '') === 'Non Tunai' || 
                   in_array($pembelian->cara_bayar ?? '', ['tempo','kredit','utang']);
        $creditAccount = $isCredit && $utangUsaha ? $utangUsaha : ($kas ?: $utangUsaha);
        if (!$creditAccount) { Log::warning('COA Kas/Utang Usaha not found'); return null; }
        $lines[] = ['account_id'=>$creditAccount->id,'debit'=>0,'credit'=>$grand,'memo'=>'Kredit pembelian'];

        return $this->createJournal($date, $reference, $desc, $lines);
    }

    // AR Payment: Dr Cash/Bank, Cr Accounts Receivable
    // If discount/selisih provided on payment:
    //   Positive selisih (diskon untuk customer): Dr Sales Discount; reduce credit to AR
    //   Negative selisih (kelebihan bayar): Cr Other Income
    public function createJournalFromPaymentAR($payment): ?Journal
    {
        $date = optional($payment->tanggal)->format('Y-m-d') ?: now()->format('Y-m-d');
        $reference = $payment->no_pembayaran ?? 'PAY-AR';
        $desc = 'Pembayaran Piutang '.$reference;

        $kas = $this->findAccount('Kas') ?: $this->findAccount('Bank');
        $piutang = $this->findAccount('Piutang Usaha');
        $diskonPenjualan = $this->findAccountAny(['Diskon Penjualan']);
        $pendapatanLain = $this->findAccountAny(['Pendapatan Lain-lain','Pend. Lain-lain','Pendapatan Lain lain']);
        if (!$kas || !$piutang) { Log::warning('COA Kas/Bank or Piutang not found'); return null; }
        $amount = (float)($payment->total_bayar ?? $payment->total_dibayar ?? $payment->jumlah ?? 0);

        // Optional discount/selisih on payment object
        $selisih = (float)($payment->diskon ?? $payment->selisih ?? 0);
        $lines = [];
        // Cash received
        if ($amount > 0) {
            $lines[] = ['account_id'=>$kas->id,'debit'=>$amount,'credit'=>0,'memo'=>'Terima pembayaran pelanggan'];
        }
        // Discount given to customer reduces AR
        if ($selisih > 0 && $diskonPenjualan) {
            $lines[] = ['account_id'=>$diskonPenjualan->id,'debit'=>$selisih,'credit'=>0,'memo'=>'Diskon penjualan saat pelunasan'];
        } elseif ($selisih < 0 && $pendapatanLain) {
            // Negative selisih -> extra income
            $lines[] = ['account_id'=>$pendapatanLain->id,'debit'=>0,'credit'=>abs($selisih),'memo'=>'Selisih lebih pembayaran pelanggan'];
        }

        $creditToAr = $amount + max(0.0, $selisih) - max(0.0, -$selisih);
        if ($creditToAr > 0) {
            $lines[] = ['account_id'=>$piutang->id,'debit'=>0,'credit'=>$creditToAr,'memo'=>'Pelunasan piutang'];
        }
        return $this->createJournal($date, $reference, $desc, $lines);
    }

    // AP Payment: Dr Accounts Payable, Cr Cash/Bank
    // If potongan/selisih provided on payment:
    //   Positive selisih (potongan dari supplier): Cr Purchase Discount
    //   Negative selisih (biaya lain-lain): Dr Other Expense
    public function createJournalFromPaymentAP($payment): ?Journal
    {
        $date = optional($payment->tanggal)->format('Y-m-d') ?: now()->format('Y-m-d');
        $reference = $payment->no_pembayaran ?? 'PAY-AP';
        $desc = 'Pembayaran Utang '.$reference;

        $utang = $this->findAccountAny(['Utang Usaha']);
        $kas = $this->findAccountAny(['Kas','Bank']);
        $diskonPembelian = $this->findAccountAny(['Diskon Pembelian']);
        $bebanLain = $this->findAccountAny(['Beban Lain-lain','Beban Lain lain']);
        if (!$utang || !$kas) { Log::warning('COA Utang/Kas not found'); return null; }
        $amount = (float)($payment->total_bayar ?? $payment->total_dibayar ?? $payment->jumlah ?? 0);
        $selisih = (float)($payment->potongan ?? $payment->selisih ?? 0);

        $lines = [];
        // Debit AP for total settlement (cash + positive potongan - negative selisih)
        $debitToAp = $amount + max(0.0,$selisih) - max(0.0,-$selisih);
        if ($debitToAp > 0) {
            $lines[] = ['account_id'=>$utang->id,'debit'=>$debitToAp,'credit'=>0,'memo'=>'Pelunasan utang'];
        }
        // Cash out
        if ($amount > 0) {
            $lines[] = ['account_id'=>$kas->id,'debit'=>0,'credit'=>$amount,'memo'=>'Pembayaran kepada supplier'];
        }
        // Positive potongan -> discount purchase (credit)
        if ($selisih > 0 && $diskonPembelian) {
            $lines[] = ['account_id'=>$diskonPembelian->id,'debit'=>0,'credit'=>$selisih,'memo'=>'Potongan pembelian saat pelunasan'];
        } elseif ($selisih < 0 && $bebanLain) {
            // Negative selisih -> other expense (debit)
            $lines[] = ['account_id'=>$bebanLain->id,'debit'=>abs($selisih),'credit'=>0,'memo'=>'Selisih biaya saat pelunasan utang'];
        }
        return $this->createJournal($date, $reference, $desc, $lines);
    }

    // Sales Return (Nota Kredit):
    // - Dr Sales Return (DPP)
    // - Dr VAT Output (reverse) if applicable
    // - Cr AR/Cash/Bank (grand total)
    // - If goods returned: Dr Inventory; Cr COGS (FIFO value of returned qty)
    public function createJournalFromSalesReturn($retur): ?Journal
    {
        $date = optional($retur->tanggal)->format('Y-m-d') ?: now()->format('Y-m-d');
        $reference = $retur->no_retur ?? 'RET-SALES';
        $desc = 'Retur Penjualan '.$reference;
        $returPenjualan = $this->findAccountAny(['Retur Penjualan']);
        $piutang = $this->findAccountAny(['Piutang Usaha']);
        $kasOrBank = $this->findAccountAny(['Kas','Bank','Kas Besar','Kas Kecil']);
        $ppnKeluaran = $this->findAccountAny(['PPN Keluaran','Utang PPN', 'Utang PPN (PPN Keluaran)']);
        $hpp = $this->findAccountAny(['COGS','Harga Pokok Penjualan (HPP)', 'Harga Pokok Penjualan']);
        $persediaan = $this->findAccountAny(['Persediaan','Persediaan Barang Dagang']);
        if (!$returPenjualan || (!$piutang && !$kasOrBank)) { Log::warning('COA for sales return not found'); return null; }

        // Determine amounts
        // We do not have explicit ppn on retur; compute from linked transaksi if available, otherwise treat total_retur as grand and get DPP by reversing ppn rate only when enabled.
        $amountGrand = (float)($retur->total_retur ?? 0);
        if (method_exists($retur, 'loadMissing')) {
            $retur->loadMissing('transaksi');
        }
        $ppnAmount = 0.0;
        if ($retur->transaksi && (float)($retur->transaksi->ppn ?? 0) > 0 && $amountGrand > 0 && (float)($retur->transaksi->grand_total ?? 0) > 0) {
            $rate = max(0.0, (float)$retur->transaksi->ppn / max(0.01,(float)$retur->transaksi->grand_total - (float)$retur->transaksi->ppn));
            // rate approximates ppn/dpp, but to avoid division issues keep simple proportional split
            $ppnAmount = round($amountGrand * ($retur->transaksi->ppn / max(0.01,$retur->transaksi->grand_total)), 2);
        }
        $dpp = max(0.0, $amountGrand - $ppnAmount);

        // Decide whether refund reduces AR or cash received
        $creditAccount = $piutang ?: $kasOrBank;
        $lines = [
            ['account_id'=>$returPenjualan->id,'debit'=>$dpp,'credit'=>0,'memo'=>'Retur penjualan (DPP)'],
        ];
        if ($ppnAmount > 0 && $ppnKeluaran) {
            $lines[] = ['account_id'=>$ppnKeluaran->id,'debit'=>$ppnAmount,'credit'=>0,'memo'=>'Pembalikan PPN Keluaran'];
        }
        $lines[] = ['account_id'=>$creditAccount->id,'debit'=>0,'credit'=>$amountGrand,'memo'=>'Koreksi piutang/kas'];

        // Inventory back if goods returned
        if ($hpp && $persediaan) {
            try {
                if (method_exists($retur, 'loadMissing')) {
                    $retur->loadMissing('items.transaksiItem.sumber');
                }
                $cogsBack = 0.0;
                foreach (($retur->items ?? []) as $ritem) {
                    $ti = $ritem->transaksiItem;
                    if (!$ti) { continue; }
                    $originalQty = (float)($ti->qty ?? 0);
                    $returnedQty = (float)($ritem->qty_retur ?? 0);
                    if ($originalQty <= 0 || $returnedQty <= 0) { continue; }
                    // Weighted average COGS/unit from original sumber
                    $totalCogsItem = 0.0; $totalQtyTaken = 0.0;
                    foreach (($ti->sumber ?? []) as $s) {
                        $totalCogsItem += ((float)$s->qty_diambil) * ((float)$s->harga_modal);
                        $totalQtyTaken += (float)$s->qty_diambil;
                    }
                    $cogsPerUnit = $totalQtyTaken > 0 ? ($totalCogsItem / $totalQtyTaken) : 0.0;
                    $cogsBack += $cogsPerUnit * $returnedQty;
                }
                if ($cogsBack > 0) {
                    $lines[] = ['account_id'=>$persediaan->id,'debit'=>$cogsBack,'credit'=>0,'memo'=>'Barang retur masuk ke persediaan'];
                    $lines[] = ['account_id'=>$hpp->id,'debit'=>0,'credit'=>$cogsBack,'memo'=>'Pembalikan HPP atas retur'];
                }
            } catch (\Exception $e) {
                Log::warning('Failed to compute inventory/HPP for sales return', ['message'=>$e->getMessage(), 'ref'=>$reference]);
            }
        }

        return $this->createJournal($date, $reference, $desc, $lines);
    }

    // Purchase Return (Nota Debit):
    // - Dr Accounts Payable / Cash-Bank (grand total)
    // - Cr Purchase Returns (DPP)
    // - Cr VAT Input (reverse)
    // - Cr Inventory (FIFO value out)
    public function createJournalFromPurchaseReturn($retur): ?Journal
    {
        $date = optional($retur->tanggal)->format('Y-m-d') ?: now()->format('Y-m-d');
        $reference = $retur->no_retur ?? 'RET-PURCHASE';
        $desc = 'Retur Pembelian '.$reference;
        $utang = $this->findAccountAny(['Utang Usaha']);
        $kasOrBank = $this->findAccountAny(['Kas','Bank','Kas Besar','Kas Kecil']);
        $returPembelianAcc = $this->findAccountAny(['Retur Pembelian']);
        $ppnMasukan = $this->findAccountAny(['PPN Masukan','Piutang PPN','Piutang PPN (PPN Masukan)']);
        $persediaan = $this->findAccountAny(['Persediaan','Persediaan Barang Dagang']);
        if ((!$utang && !$kasOrBank) || !$persediaan || !$returPembelianAcc) { Log::warning('COA for purchase return not found'); return null; }

        $amountGrand = (float)($retur->total_retur ?? 0);
        // Derive DPP and PPN from linked pembelian if exists
        if (method_exists($retur, 'loadMissing')) {
            $retur->loadMissing('pembelian');
        }
        $ppnAmount = 0.0;
        if ($retur->pembelian && (float)($retur->pembelian->ppn ?? 0) > 0 && (float)($retur->pembelian->grand_total ?? 0) > 0) {
            $ppnAmount = round($amountGrand * ($retur->pembelian->ppn / max(0.01,$retur->pembelian->grand_total)), 2);
        }
        $dpp = max(0.0, $amountGrand - $ppnAmount);

        // Assume increases supplier receivable (reduce AP) by grand total
        $debitAccount = $utang ?: $kasOrBank;
        $lines = [
            ['account_id'=>$debitAccount->id,'debit'=>$amountGrand,'credit'=>0,'memo'=>'Koreksi utang/kas karena retur pembelian'],
            ['account_id'=>$returPembelianAcc->id,'debit'=>0,'credit'=>$dpp,'memo'=>'Retur pembelian (DPP)'],
        ];
        if ($ppnAmount > 0 && $ppnMasukan) {
            $lines[] = ['account_id'=>$ppnMasukan->id,'debit'=>0,'credit'=>$ppnAmount,'memo'=>'Pembalikan PPN Masukan'];
        }

        // Inventory out at purchase price per returned item (linked to pembelian items)
        if ($persediaan) {
            try {
                if (method_exists($retur, 'loadMissing')) {
                    $retur->loadMissing('items.pembelianItem');
                }
                $invOut = 0.0;
                foreach (($retur->items ?? []) as $ritem) {
                    $price = (float)optional($ritem->pembelianItem)->harga ?: 0.0;
                    $qty = (float)($ritem->qty_retur ?? 0);
                    if ($price > 0 && $qty > 0) {
                        $invOut += $price * $qty;
                    }
                }
                if ($invOut > 0) {
                    $lines[] = ['account_id'=>$persediaan->id,'debit'=>0,'credit'=>$invOut,'memo'=>'Pengurangan persediaan karena retur pembelian'];
                }
            } catch (\Exception $e) {
                Log::warning('Failed to compute inventory for purchase return', ['message'=>$e->getMessage(), 'ref'=>$reference]);
            }
        }

        return $this->createJournal($date, $reference, $desc, $lines);
    }

    // Misc cash-in: Dr Cash/Bank, Cr Other Income (or provided income account)
    public function createJournalCashIn(string $date, string $reference, float $amount, string $incomeAccountName = 'Pendapatan Lain-lain'): ?Journal
    {
        $kas = $this->findAccountAny(['Kas','Bank','Kas Besar','Kas Kecil']);
        $income = $this->findAccountAny([$incomeAccountName,'Pend. Lain-lain','Pendapatan Lain-lain']);
        if (!$kas || !$income) { Log::warning('COA for cash-in not found'); return null; }
        $lines = [
            ['account_id'=>$kas->id,'debit'=>$amount,'credit'=>0,'memo'=>'Kas masuk lainnya'],
            ['account_id'=>$income->id,'debit'=>0,'credit'=>$amount,'memo'=>'Pendapatan lain-lain'],
        ];
        return $this->createJournal($date, $reference, 'Kas Masuk Lainnya '.$reference, $lines);
    }

    // Misc cash-out: Dr Expense, Cr Cash/Bank
    public function createJournalCashOut(string $date, string $reference, float $amount, string $expenseAccountName = 'Beban Lain-lain'): ?Journal
    {
        $kas = $this->findAccountAny(['Kas','Bank','Kas Besar','Kas Kecil']);
        $expense = $this->findAccountAny([$expenseAccountName,'Beban Lain-lain']);
        if (!$kas || !$expense) { Log::warning('COA for cash-out not found'); return null; }
        $lines = [
            ['account_id'=>$expense->id,'debit'=>$amount,'credit'=>0,'memo'=>'Beban kas keluar'],
            ['account_id'=>$kas->id,'debit'=>0,'credit'=>$amount,'memo'=>'Kas keluar'],
        ];
        return $this->createJournal($date, $reference, 'Kas Keluar '.$reference, $lines);
    }

    // Bank loan disbursement: Dr Bank, Cr Bank Loan
    public function createJournalBankLoanDisbursement(string $date, string $reference, float $amount): ?Journal
    {
        $bank = $this->findAccountAny(['Bank','Kas','Kas Besar','Kas Kecil']);
        $loan = $this->findAccountAny(['Utang Bank']);
        if (!$bank || !$loan) { Log::warning('COA for bank loan disbursement not found'); return null; }
        $lines = [
            ['account_id'=>$bank->id,'debit'=>$amount,'credit'=>0,'memo'=>'Pencairan pinjaman bank'],
            ['account_id'=>$loan->id,'debit'=>0,'credit'=>$amount,'memo'=>'Utang bank'],
        ];
        return $this->createJournal($date, $reference, 'Pencairan Pinjaman Bank '.$reference, $lines);
    }

    // Bank loan installment: Dr Bank Loan (principal), Dr Interest Expense, Cr Bank
    public function createJournalBankLoanInstallment(string $date, string $reference, float $principalAmount, float $interestAmount, string $interestExpenseAccountName = 'Beban Bunga'): ?Journal
    {
        $bank = $this->findAccountAny(['Bank','Kas','Kas Besar','Kas Kecil']);
        $loan = $this->findAccountAny(['Utang Bank']);
        $interestExp = $this->findAccountAny([$interestExpenseAccountName]);
        if (!$bank || !$loan || !$interestExp) { Log::warning('COA for bank loan installment not found'); return null; }
        $total = $principalAmount + $interestAmount;
        $lines = [
            ['account_id'=>$loan->id,'debit'=>$principalAmount,'credit'=>0,'memo'=>'Angsuran pokok utang bank'],
            ['account_id'=>$interestExp->id,'debit'=>$interestAmount,'credit'=>0,'memo'=>'Beban bunga'],
            ['account_id'=>$bank->id,'debit'=>0,'credit'=>$total,'memo'=>'Pembayaran angsuran ke bank'],
        ];
        return $this->createJournal($date, $reference, 'Angsuran Pinjaman Bank '.$reference, $lines);
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


