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
    // - Tunai: Dr Kas Besar/Kecil; Kredit: Dr Piutang Usaha; Non Tunai: Dr Bank 1104-x
    // - Cr Pendapatan Penjualan (= DPP)
    // - Cr PPN Keluaran (= PPN) [DB2]
    // - Dr HPP; Cr Persediaan (= cogs)
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

        // Decide payment method: cash, credit, or non-cash
        $isCash = false;
        $isCredit = false;
        $caraBayar = strtolower((string)($transaksi->cara_bayar ?? ''));
        $pembayaran = strtolower((string)($transaksi->pembayaran ?? ''));
        
        if (in_array($caraBayar, ['tunai','cash']) || in_array($pembayaran, ['tunai','cash'])) {
            $isCash = true;
        } elseif (in_array($caraBayar, ['kredit','credit','tempo','utang']) || in_array($pembayaran, ['kredit','credit','tempo','utang'])) {
            $isCredit = true;
        }

        $lines = [];
        if ($isCash) {
            // Prefer specific Kas Kecil/Kas Besar if indicated
            $preferredKas = $this->resolveKasAccountByText($transaksi->cara_bayar ?? null, $transaksi->pembayaran ?? null);
            if (!$preferredKas) {
                $preferredKas = $this->findAccountAny(['Kas Besar', 'Kas Kecil', 'Kas']);
            }
            if ($preferredKas) {
                $lines[] = ['account_id'=>$preferredKas->id,'debit'=>$grand,'credit'=>0,'memo'=>'Penerimaan penjualan tunai'];
            } else {
                Log::warning('COA Kas Besar/Kecil not found for cash sales');
                return null;
            }
        } elseif ($isCredit) {
            // Penjualan Kredit: Dr Piutang Usaha
            if ($piutang) {
                $lines[] = ['account_id'=>$piutang->id,'debit'=>$grand,'credit'=>0,'memo'=>'Piutang usaha penjualan'];
            } else {
                Log::warning('COA Piutang Usaha not found for credit sales');
                return null;
            }
        } else {
            // Penjualan Non Tunai (Bank Transfer): Dr Bank spesifik bila ada, else 1104-x
            $preferredBank = $this->resolveBankAccountByText($transaksi->cara_bayar ?? null, $transaksi->pembayaran ?? null);
            $bankAccount = $preferredBank ?: $this->findAccountAny(['Bank', '1104-1', '1104-2', '1104-3', '1104-4']);
            if ($bankAccount) {
                $lines[] = ['account_id'=>$bankAccount->id,'debit'=>$grand,'credit'=>0,'memo'=>'Penerimaan penjualan non tunai'];
            } else {
                // Fallback to AR if no bank account found
                $lines[] = ['account_id'=>$piutang->id,'debit'=>$grand,'credit'=>0,'memo'=>'Piutang usaha penjualan'];
            }
        }
        $lines[] = ['account_id'=>$pendapatan->id,'debit'=>0,'credit'=>$sales,'memo'=>'Pendapatan penjualan'];
        if ($ppn > 0 && $utangPpn) {
            $lines[] = ['account_id'=>$utangPpn->id,'debit'=>0,'credit'=>$ppn,'memo'=>'PPN Keluaran'];
        }
        // HPP: Dr HPP; Cr Persediaan (= cogs)
        if ($hpp && $persediaan) {
            try {
                $cogs = 0.0;
                if (method_exists($transaksi, 'loadMissing')) {
                    $transaksi->loadMissing('items.transaksiItemSumber.stockBatch');
                }
                foreach (($transaksi->items ?? []) as $item) {
                    $sumbers = $item->transaksiItemSumber ?? ($item->sumber ?? []);
                    foreach ($sumbers as $sumber) {
                        if ($sumber->stockBatch) {
                            $cogs += ((float)$sumber->qty_diambil) * ((float)$sumber->stockBatch->harga_beli);
                        }
                    }
                }
                if ($cogs > 0) {
                    $lines[] = ['account_id'=>$hpp->id,'debit'=>$cogs,'credit'=>0,'memo'=>'HPP'];
                    $lines[] = ['account_id'=>$persediaan->id,'debit'=>0,'credit'=>$cogs,'memo'=>'Pengurangan persediaan (HPP)'];
                }
            } catch (\Exception $e) {
                Log::warning('Failed to compute HPP for sales journal', ['message'=>$e->getMessage(), 'ref'=>$reference]);
            }
        }
        return $this->createJournal($date, $reference, $desc, $lines);
    }

    // Purchase: 
    // - Tunai/Transfer: Dr Persediaan, Dr PPN Masukan, Cr Bank 1104-x
    // - Kredit: Dr Persediaan, Dr PPN Masukan, Cr Utang Usaha
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
            $lines[] = ['account_id'=>$piutangPpn->id,'debit'=>0,'credit'=>0 + $ppn,'memo'=>'PPN Masukan'];
        }

        // Determine payment method: Tunai/Transfer = Bank, Kredit = Utang Usaha
        $isCredit = in_array($pembelian->cara_bayar ?? '', ['tempo','kredit','utang']);
        
        if ($isCredit) {
            // Pembelian Kredit: Cr Utang Usaha
            if ($utangUsaha) {
                $lines[] = ['account_id'=>$utangUsaha->id,'debit'=>0,'credit'=>$grand,'memo'=>'Utang pembelian'];
            } else {
                Log::warning('COA Utang Usaha not found for credit purchase');
                return null;
            }
        } else {
            // Pembelian tunai / non-kredit.
            // PRIORITAS:
            // 1) Jika cara_bayar/pembayaran mengandung "kas kecil"/"kas besar" => gunakan akun Kas sesuai teks.
            // 2) Selain itu, gunakan akun Bank (spesifik jika bisa di-resolve dari teks).
            $kasAccount = $this->resolveKasAccountByText($pembelian->cara_bayar ?? null, $pembelian->pembayaran ?? null);

            if ($kasAccount) {
                // Tunai via Kas (Kas Kecil / Kas Besar)
                $lines[] = ['account_id'=>$kasAccount->id,'debit'=>0,'credit'=>$grand,'memo'=>'Pembayaran pembelian tunai (kas)'];
            } else {
                // Tunai/Transfer via Bank
                $preferredBank = $this->resolveBankAccountByText($pembelian->cara_bayar ?? null, $pembelian->pembayaran ?? null);
                $bankAccount = $preferredBank ?: $this->findAccountAny(['Bank', '1104-1', '1104-2', '1104-3', '1104-4']);

                if ($bankAccount) {
                    $lines[] = ['account_id'=>$bankAccount->id,'debit'=>0,'credit'=>$grand,'memo'=>'Pembayaran pembelian tunai/transfer (bank)'];
                } else {
                    // Fallback terakhir: cari akun Kas umum jika tidak ada akun bank spesifik
                    $fallbackKas = $this->findAccountAny(['Kas Besar', 'Kas Kecil', 'Kas']);
                    if ($fallbackKas) {
                        $lines[] = ['account_id'=>$fallbackKas->id,'debit'=>0,'credit'=>$grand,'memo'=>'Pembayaran pembelian tunai (kas)'];
                    } else {
                        Log::warning('COA Bank/Kas not found for cash purchase');
                        return null;
                    }
                }
            }
        }

        return $this->createJournal($date, $reference, $desc, $lines);
    }

    // AR Payment: Dr Bank 1104-x / Kas Besar/Kecil, Cr Piutang Usaha
    // Jika selisih: Dr Diskon Penjualan (potongan) / Cr Pendapatan Lain-lain (kelebihan)
    public function createJournalFromPaymentAR($payment): ?Journal
    {
        $date = optional($payment->tanggal)->format('Y-m-d') ?: now()->format('Y-m-d');
        $reference = $payment->no_pembayaran ?? 'PAY-AR';
        $desc = 'Pembayaran Piutang '.$reference;

        // Prioritas akun: Bank spesifik → 1104-x → Kas Besar/Kecil → Kas
        $preferredKas = $this->resolveKasAccountByText($payment->cara_bayar ?? null, $payment->pembayaran ?? null);
        $preferredBank = $this->resolveBankAccountByText($payment->cara_bayar ?? null, $payment->pembayaran ?? null);
        $bankOrKas = $preferredBank ?: ($preferredKas ?: $this->findAccountAny(['Bank', '1104-1', '1104-2', '1104-3', '1104-4', 'Kas Besar', 'Kas Kecil', 'Kas']));
        $piutang = $this->findAccount('Piutang Usaha');
        $diskonPenjualan = $this->findAccountAny(['Diskon Penjualan']);
        $pendapatanLain = $this->findAccountAny(['Pendapatan Lain-lain','Pend. Lain-lain','Pendapatan Lain lain']);
        
        if (!$bankOrKas || !$piutang) { 
            Log::warning('COA Bank/Kas or Piutang not found'); 
            return null; 
        }
        
        $amount = (float)($payment->total_bayar ?? $payment->total_dibayar ?? $payment->jumlah ?? 0);
        $selisih = (float)($payment->diskon ?? $payment->selisih ?? 0);
        
        $lines = [];
        
        // Dr Bank 1104-x / Kas Besar/Kecil (= diterima)
        if ($amount > 0) {
            $lines[] = ['account_id'=>$bankOrKas->id,'debit'=>$amount,'credit'=>0,'memo'=>'Terima pembayaran pelanggan'];
        }
        
        // Jika selisih: Dr Diskon Penjualan (potongan) / Cr Pendapatan Lain-lain (kelebihan)
        if ($selisih > 0 && $diskonPenjualan) {
            // Positive selisih = potongan untuk customer
            $lines[] = ['account_id'=>$diskonPenjualan->id,'debit'=>$selisih,'credit'=>0,'memo'=>'Diskon penjualan saat pelunasan'];
        } elseif ($selisih < 0 && $pendapatanLain) {
            // Negative selisih = kelebihan bayar
            $lines[] = ['account_id'=>$pendapatanLain->id,'debit'=>0,'credit'=>abs($selisih),'memo'=>'Selisih lebih pembayaran pelanggan'];
        }

        // Cr Piutang Usaha (= diterima)
        $creditToAr = $amount + max(0.0, $selisih) - max(0.0, -$selisih);
        if ($creditToAr > 0) {
            $lines[] = ['account_id'=>$piutang->id,'debit'=>0,'credit'=>$creditToAr,'memo'=>'Pelunasan piutang'];
        }
        return $this->createJournal($date, $reference, $desc, $lines);
    }

    // AP Payment: Dr Utang Usaha, Cr Bank 1104-x / Kas Besar/Kecil
    // Jika potongan: Cr Diskon Pembelian (potongan) / Dr Beban Lain-lain (selisih biaya)
    public function createJournalFromPaymentAP($payment): ?Journal
    {
        $date = optional($payment->tanggal)->format('Y-m-d') ?: now()->format('Y-m-d');
        $reference = $payment->no_pembayaran ?? 'PAY-AP';
        $desc = 'Pembayaran Utang '.$reference;

        // Prioritas akun: Bank spesifik → 1104-x → Kas Besar/Kecil → Kas
        $preferredKas = $this->resolveKasAccountByText($payment->cara_bayar ?? null, $payment->pembayaran ?? null);
        $preferredBank = $this->resolveBankAccountByText($payment->cara_bayar ?? null, $payment->pembayaran ?? null);
        $bankOrKas = $preferredBank ?: ($preferredKas ?: $this->findAccountAny(['Bank', '1104-1', '1104-2', '1104-3', '1104-4', 'Kas Besar', 'Kas Kecil', 'Kas']));
        $utang = $this->findAccountAny(['Utang Usaha']);
        $diskonPembelian = $this->findAccountAny(['Diskon Pembelian']);
        $bebanLain = $this->findAccountAny(['Beban Lain-lain','Beban Lain lain']);
        
        if (!$utang || !$bankOrKas) { 
            Log::warning('COA Utang Usaha or Bank/Kas not found'); 
            return null; 
        }
        
        $amount = (float)($payment->total_bayar ?? $payment->total_dibayar ?? $payment->jumlah ?? 0);
        $potongan = (float)($payment->potongan ?? $payment->selisih ?? 0);
        
        $lines = [];
        
        // Dr Utang Usaha (= dibayar)
        if ($amount > 0) {
            $lines[] = ['account_id'=>$utang->id,'debit'=>$amount,'credit'=>0,'memo'=>'Pelunasan utang'];
        }
        
        // Cr Bank 1104-x / Kas Besar/Kecil (= dibayar)
        if ($amount > 0) {
            $lines[] = ['account_id'=>$bankOrKas->id,'debit'=>0,'credit'=>$amount,'memo'=>'Pembayaran kepada supplier'];
        }
        
        // Jika potongan: Cr Diskon Pembelian (potongan) / Dr Beban Lain-lain (selisih biaya)
        if ($potongan > 0 && $diskonPembelian) {
            // Positive potongan = diskon dari supplier
            $lines[] = ['account_id'=>$diskonPembelian->id,'debit'=>0,'credit'=>$potongan,'memo'=>'Potongan pembelian saat pelunasan'];
        } elseif ($potongan < 0 && $bebanLain) {
            // Negative potongan = selisih biaya
            $lines[] = ['account_id'=>$bebanLain->id,'debit'=>abs($potongan),'credit'=>0,'memo'=>'Selisih biaya saat pelunasan utang'];
        }
        return $this->createJournal($date, $reference, $desc, $lines);
    }

    // Sales Return (Nota Kredit):
    // - Dr Retur Penjualan (= DPP)
    // - Dr PPN Keluaran (= PPN) [DB2]
    // - Cr Piutang Usaha / Kas/Bank (= grand total)
    // - Jika barang kembali: Dr Persediaan; Cr HPP (= nilai FIFO kembali)
    public function createJournalFromSalesReturn($retur): ?Journal
    {
        $date = optional($retur->tanggal)->format('Y-m-d') ?: now()->format('Y-m-d');
        $reference = $retur->no_retur ?? 'RET-SALES';
        $desc = 'Retur Penjualan '.$reference;
        $returPenjualan = $this->findAccountAny(['Retur Penjualan']);
        $piutang = $this->findAccountAny(['Piutang Usaha']);
        // Prioritas akun: Bank 1104-x → Kas Besar/Kecil → Kas
        $kasOrBank = $this->findAccountAny(['Bank', '1104-1', '1104-2', '1104-3', '1104-4', 'Kas Besar', 'Kas Kecil', 'Kas']);
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

        // Determine jenis retur berdasarkan transaksi asal
        // Retur Kredit: jika transaksi asal benar‑benar penjualan kredit (ngutang) → potong Piutang Usaha
        // Retur Tunai/Non Tunai: jika transaksi asal dibayar langsung (cash / transfer) → refund ke Kas/Bank
        $transaksi = $retur->transaksi;
        $isReturKredit = false;
        $creditAccount = null;
        $creditMemo = 'Koreksi karena retur penjualan';
        
        if ($transaksi) {
            // Deteksi penjualan kredit harus SAMA dengan logika di createJournalFromSale,
            // supaya jurnal retur selalu konsisten dengan jurnal penjualannya.
            $caraBayarText = strtolower((string)($transaksi->cara_bayar ?? ''));
            $pembayaranText = strtolower((string)($transaksi->pembayaran ?? ''));

            $isCashSale = in_array($caraBayarText, ['tunai','cash']) || in_array($pembayaranText, ['tunai','cash']);
            $isCreditSale = in_array($caraBayarText, ['kredit','credit','tempo','utang']) 
                || in_array($pembayaranText, ['kredit','credit','tempo','utang']);

            if ($isCreditSale && !$isCashSale) {
                // Retur Kredit: potong Piutang Usaha
                // Tapi cek dulu apakah piutang sudah lunas
                if ($transaksi->status_piutang === 'lunas') {
                    Log::warning('Cannot create return for fully paid credit transaction', [
                        'transaksi_id' => $transaksi->id,
                        'no_retur' => $reference
                    ]);
                    return null; // Tidak bisa retur jika sudah lunas
                }
                
                $isReturKredit = true;
                $creditAccount = $piutang;
                $creditMemo = 'Koreksi piutang karena retur penjualan';
            } else {
                // Retur Tunai/Non Tunai: refund ke Kas/Bank sesuai cara_bayar transaksi asal
                // Gunakan COA yang sama dengan transaksi asal
                $caraBayarText = $transaksi->cara_bayar ?? '';
                
                // Cari CaraBayar berdasarkan nama cara_bayar dari transaksi
                $caraBayar = \App\Models\CaraBayar::where('nama', $caraBayarText)->first();
                
                if ($caraBayar && $caraBayar->hasCoaAccount()) {
                    // Gunakan COA yang terhubung dengan cara_bayar
                    $creditAccount = $caraBayar->coaAccount;
                    $creditMemo = 'Refund untuk retur penjualan';
                } else {
                    // Fallback: cari COA berdasarkan teks cara_bayar
                    $pembayaranText = $transaksi->pembayaran ?? '';
                    
                    // Jika tunai, gunakan Kas Besar/Kas Kecil
                    if (stripos($caraBayarText, 'tunai') !== false || stripos($pembayaranText, 'tunai') !== false) {
                        $creditAccount = $this->resolveKasAccountByText($caraBayarText, $pembayaranText);
                        if (!$creditAccount) {
                            // Fallback ke Kas Kecil atau Kas Besar
                            $creditAccount = $this->findAccountAny(['Kas Kecil', 'Kas Besar', 'Kas']);
                        }
                        $creditMemo = 'Refund uang cash untuk retur penjualan';
                    } else {
                        // Non tunai: gunakan Bank sesuai cara_bayar
                        $creditAccount = $this->resolveBankAccountByText($caraBayarText, $pembayaranText);
                        if (!$creditAccount) {
                            // Fallback ke Bank umum
                            $creditAccount = $this->findAccountAny(['Bank', '1104-1', '1104-2', '1104-3', '1104-4']);
                        }
                        $creditMemo = 'Refund ke bank untuk retur penjualan';
                    }
                }
            }
        }
        
        // Fallback jika tidak ada transaksi atau tidak ditemukan akun
        if (!$creditAccount) {
            if ($isReturKredit) {
                $creditAccount = $piutang;
            } else {
                $creditAccount = $kasOrBank;
            }
        }
        
        $lines = [
            // Dr Retur Penjualan (= DPP)
            ['account_id'=>$returPenjualan->id,'debit'=>$dpp,'credit'=>0,'memo'=>'Retur penjualan (DPP)'],
        ];
        // Dr PPN Keluaran (= PPN) - Pastikan PPN juga di-retur
        if ($ppnAmount > 0 && $ppnKeluaran) {
            $lines[] = ['account_id'=>$ppnKeluaran->id,'debit'=>$ppnAmount,'credit'=>0,'memo'=>'Pembalikan PPN Keluaran'];
        }
        // Cr Piutang Usaha (retur kredit) atau Kas/Bank (retur tunai) (= grand total)
        $lines[] = ['account_id'=>$creditAccount->id,'debit'=>0,'credit'=>$amountGrand,'memo'=>$creditMemo];

        // Jika barang kembali: Dr Persediaan; Cr HPP (= nilai FIFO kembali)
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
                    // Weighted average COGS/unit from original sumber (FIFO)
                    $totalCogsItem = 0.0; $totalQtyTaken = 0.0;
                    foreach (($ti->sumber ?? []) as $s) {
                        $totalCogsItem += ((float)$s->qty_diambil) * ((float)$s->harga_modal);
                        $totalQtyTaken += (float)$s->qty_diambil;
                    }
                    $cogsPerUnit = $totalQtyTaken > 0 ? ($totalCogsItem / $totalQtyTaken) : 0.0;
                    $cogsBack += $cogsPerUnit * $returnedQty;
                }
                if ($cogsBack > 0) {
                    // Dr Persediaan; Cr HPP (= nilai FIFO kembali)
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

        // Determine jenis retur berdasarkan pembelian asal
        // Retur Kredit: jika pembelian asal pembayaran = 'Kredit' (potong Utang Usaha)
        // Retur Tunai: jika pembelian asal pembayaran = 'Tunai' atau cara_bayar = tunai/non tunai (refund dari Kas/Bank)
        $pembelian = $retur->pembelian;
        $isReturKredit = false;
        $debitAccount = null;
        $debitMemo = 'Koreksi karena retur pembelian';
        
        if ($pembelian) {
            // Deteksi pembelian kredit harus sama dengan logika di createJournalFromPurchase
            $caraBayarText = strtolower((string)($pembelian->cara_bayar ?? ''));
            $pembayaranText = strtolower((string)($pembelian->pembayaran ?? ''));
            $isCreditPurchase = in_array($caraBayarText, ['tempo','kredit','utang'])
                || in_array($pembayaranText, ['tempo','kredit','utang']);

            if ($isCreditPurchase) {
                // Retur Kredit: potong Utang Usaha
                $isReturKredit = true;
                $debitAccount = $utang;
                $debitMemo = 'Koreksi utang karena retur pembelian';
            } else {
                // Retur Tunai/Non Tunai: refund dari Kas/Bank sesuai cara_bayar pembelian asal
                // Gunakan COA yang sama dengan pembelian asal
                $caraBayarText = $pembelian->cara_bayar ?? '';
                
                // Cari CaraBayar berdasarkan nama cara_bayar dari pembelian
                $caraBayar = \App\Models\CaraBayar::where('nama', $caraBayarText)->first();
                
                if ($caraBayar && $caraBayar->hasCoaAccount()) {
                    // Gunakan COA yang terhubung dengan cara_bayar
                    $debitAccount = $caraBayar->coaAccount;
                    $debitMemo = 'Refund untuk retur pembelian';
                } else {
                    // Fallback: cari COA berdasarkan teks cara_bayar
                    $pembayaranText = $pembelian->pembayaran ?? '';
                    
                    // Jika tunai, gunakan Kas Besar/Kas Kecil
                    if (stripos($caraBayarText, 'tunai') !== false || stripos($pembayaranText, 'tunai') !== false) {
                        $debitAccount = $this->resolveKasAccountByText($caraBayarText, $pembayaranText);
                        if (!$debitAccount) {
                            // Fallback ke Kas Kecil atau Kas Besar
                            $debitAccount = $this->findAccountAny(['Kas Kecil', 'Kas Besar', 'Kas']);
                        }
                        $debitMemo = 'Refund uang cash untuk retur pembelian';
                    } else {
                        // Non tunai: gunakan Bank sesuai cara_bayar
                        $debitAccount = $this->resolveBankAccountByText($caraBayarText, $pembayaranText);
                        if (!$debitAccount) {
                            // Fallback ke Bank umum
                            $debitAccount = $this->findAccountAny(['Bank', '1104-1', '1104-2', '1104-3', '1104-4']);
                        }
                        $debitMemo = 'Refund dari bank untuk retur pembelian';
                    }
                }
            }
        }
        
        // Fallback jika tidak ada pembelian atau tidak ditemukan akun
        if (!$debitAccount) {
            if ($isReturKredit) {
                $debitAccount = $utang;
            } else {
                $debitAccount = $kasOrBank;
            }
        }
        
        $lines = [
            ['account_id'=>$debitAccount->id,'debit'=>$amountGrand,'credit'=>0,'memo'=>$debitMemo],
            ['account_id'=>$returPembelianAcc->id,'debit'=>0,'credit'=>$dpp,'memo'=>'Retur pembelian (DPP)'],
        ];
        // Cr PPN Masukan (= PPN) - Pastikan PPN juga di-retur
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
            ['account_id'=>$interestExp->id,'debit'=>0,'credit'=>$interestAmount,'memo'=>'Beban bunga'],
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

    /**
     * Resolve default Cash/Bank account by priority when user doesn't pick one.
     * Priority: Kas Besar → Kas Kecil → 1104-1 → 1104-2 → 1104-3 → 1104-4 → Bank (1104)
     */
    public function resolveDefaultCashBank(): ?ChartOfAccount
    {
        // 1) Names priority
        $orderByName = ['Kas Besar', 'Kas Kecil'];
        foreach ($orderByName as $n) {
            $acc = $this->findAccount($n);
            if ($acc) return $acc;
        }
        // 2) Bank sub-accounts by codes
        $orderByCode = ['1104-1','1104-2','1104-3','1104-4'];
        foreach ($orderByCode as $code) {
            $acc = $this->findAccountByCode($code);
            if ($acc) return $acc;
        }
        // 3) Fallback to Bank (group) by name or code 1104
        $bank = $this->findAccount('Bank');
        if ($bank) return $bank;
        $bankByCode = $this->findAccountByCode('1104');
        if ($bankByCode) return $bankByCode;
        return null;
    }

    private function findAccountByCode(string $code): ?ChartOfAccount
    {
        return ChartOfAccount::where('code', $code)->first();
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

    /**
     * Get bank child accounts (QRIS, EDC, Giro) for a specific bank
     */
    public function getBankChildAccounts(string $bankCode, ?int $periodId = null): \Illuminate\Database\Eloquent\Collection
    {
        return ChartOfAccount::where('code', 'like', $bankCode . '%')
            ->where(function($query) {
                $query->where('code', 'like', '%-QRIS')
                    ->orWhere('code', 'like', '%-EDC')
                    ->orWhere('code', 'like', '%-GIRO');
            })
            ->where('is_active', true)
            ->with(['parent', 'accountType'])
            ->get()
            ->map(function($account) use ($periodId) {
                $account->current_balance = $account->calculateBalance($periodId);
                return $account;
            });
    }

    /**
     * Get QRIS accounts
     */
    public function getQrisAccounts(?int $periodId = null): \Illuminate\Database\Eloquent\Collection
    {
        return ChartOfAccount::qrisAccounts()
            ->where('is_active', true)
            ->with(['parent', 'accountType'])
            ->get()
            ->map(function($account) use ($periodId) {
                $account->current_balance = $account->calculateBalance($periodId);
                return $account;
            });
    }

    /**
     * Get EDC accounts
     */
    public function getEdcAccounts(?int $periodId = null): \Illuminate\Database\Eloquent\Collection
    {
        return ChartOfAccount::edcAccounts()
            ->where('is_active', true)
            ->with(['parent', 'accountType'])
            ->get()
            ->map(function($account) use ($periodId) {
                $account->current_balance = $account->calculateBalance($periodId);
                return $account;
            });
    }

    /**
     * Get Giro accounts
     */
    public function getGiroAccounts(?int $periodId = null): \Illuminate\Database\Eloquent\Collection
    {
        return ChartOfAccount::giroAccounts()
            ->where('is_active', true)
            ->with(['parent', 'accountType'])
            ->get()
            ->map(function($account) use ($periodId) {
                $account->current_balance = $account->calculateBalance($periodId);
                return $account;
            });
    }

    /**
     * Find bank child account by payment method
     */
    public function findBankChildAccountByPaymentMethod(string $paymentMethod, ?string $bankCode = null): ?ChartOfAccount
    {
        $query = ChartOfAccount::where('is_active', true);
        
        if ($bankCode) {
            $query->where('code', 'like', $bankCode . '%');
        }
        
        $paymentMethod = strtoupper($paymentMethod);
        
        switch ($paymentMethod) {
            case 'QRIS':
                return $query->where('code', 'like', '%-QRIS')->first();
            case 'EDC':
                return $query->where('code', 'like', '%-EDC')->first();
            case 'GIRO':
                return $query->where('code', 'like', '%-GIRO')->first();
            default:
                return null;
        }
    }

    /**
     * Create or link COA account for CaraBayar
     */
    public function createOrLinkCoaAccountForCaraBayar(\App\Models\CaraBayar $caraBayar): ?ChartOfAccount
    {
        $expectedCoaCode = $caraBayar->getExpectedCoaCode();
        
        if (!$expectedCoaCode) {
            return null;
        }

        // Try to find existing COA account
        $existingAccount = ChartOfAccount::where('code', $expectedCoaCode)
            ->where('is_active', true)
            ->first();

        if ($existingAccount) {
            return $existingAccount;
        }

        // If not found, try to create one based on the payment method
        return $this->createCoaAccountForCaraBayar($caraBayar, $expectedCoaCode);
    }

    /**
     * Create new COA account for CaraBayar
     */
    private function createCoaAccountForCaraBayar(\App\Models\CaraBayar $caraBayar, string $expectedCoaCode): ?ChartOfAccount
    {
        $assetTypeId = \App\Models\AccountType::where('code', 'A')->value('id');
        
        if (!$assetTypeId) {
            Log::error('Asset account type not found when creating COA for CaraBayar', [
                'cara_bayar_id' => $caraBayar->id,
                'expected_code' => $expectedCoaCode
            ]);
            return null;
        }

        $accountName = $this->generateCoaAccountNameForCaraBayar($caraBayar);
        $parentId = $this->findParentAccountIdForCaraBayar($caraBayar);

        try {
            $coaAccount = ChartOfAccount::create([
                'code' => $expectedCoaCode,
                'name' => $accountName,
                'account_type_id' => $assetTypeId,
                'parent_id' => $parentId,
                'is_active' => true,
                'balance' => 0,
            ]);

            Log::info('Created new COA account for CaraBayar', [
                'cara_bayar_id' => $caraBayar->id,
                'coa_account_id' => $coaAccount->id,
                'code' => $expectedCoaCode,
                'name' => $accountName
            ]);

            return $coaAccount;
        } catch (\Exception $e) {
            Log::error('Failed to create COA account for CaraBayar', [
                'cara_bayar_id' => $caraBayar->id,
                'expected_code' => $expectedCoaCode,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Generate COA account name for CaraBayar
     */
    private function generateCoaAccountNameForCaraBayar(\App\Models\CaraBayar $caraBayar): string
    {
        if ($caraBayar->isCash()) {
            if ($caraBayar->isQris() || $caraBayar->isEdc() || $caraBayar->isGiro()) {
                return 'Kas ' . ucfirst(strtolower($caraBayar->nama));
            }
            
            if (strpos(strtoupper($caraBayar->nama), 'KECIL') !== false) {
                return 'Kas Kecil';
            } elseif (strpos(strtoupper($caraBayar->nama), 'BESAR') !== false) {
                return 'Kas Besar';
            }
            
            return 'Kas ' . $caraBayar->nama;
        }

        // For bank-related payments
        $bankCode = $caraBayar->getBankCode();
        if ($bankCode) {
            $bankName = $this->getBankNameFromCode($bankCode);
            
            if ($caraBayar->isQris()) {
                return $bankName . ' - QRIS';
            } elseif ($caraBayar->isEdc()) {
                return $bankName . ' - EDC';
            } elseif ($caraBayar->isGiro()) {
                return $bankName . ' - Giro';
            }
        }

        return $caraBayar->nama;
    }

    /**
     * Get bank name from bank code
     */
    private function getBankNameFromCode(string $bankCode): string
    {
        switch ($bankCode) {
            case '1104-1':
                return 'Bank Mandiri';
            case '1104-2':
                return 'Bank BNI';
            case '1104-3':
                return 'Bank BRI';
            case '1104-4':
                return 'Bank BCA';
            default:
                return 'Bank';
        }
    }

    /**
     * Find parent account ID for CaraBayar
     */
    private function findParentAccountIdForCaraBayar(\App\Models\CaraBayar $caraBayar): ?int
    {
        if ($caraBayar->isCash()) {
            // Cash accounts don't need parent
            return null;
        }

        $bankCode = $caraBayar->getBankCode();
        if ($bankCode) {
            return ChartOfAccount::where('code', $bankCode)->value('id');
        }

        return null;
    }

    /**
     * Pick specific Kas account by payment text. Returns Kas Kecil if the text contains "kecil",
     * returns Kas Besar if it contains "besar". Case-insensitive. Falls back to null.
     */
    private function resolveKasAccountByText(?string $caraBayar, ?string $pembayaran): ?ChartOfAccount
    {
        $text = strtolower(trim(($caraBayar ?? '').' '.($pembayaran ?? '')));
        if ($text === '') { return null; }
        if (strpos($text, 'kecil') !== false) {
            return $this->findAccount('Kas Kecil');
        }
        if (strpos($text, 'besar') !== false) {
            return $this->findAccount('Kas Besar');
        }
        // Also catch exact names like "cash (kas kecil)" or "cash (kas besar)"
        if (strpos($text, 'cash (kas kecil)') !== false) {
            return $this->findAccount('Kas Kecil');
        }
        if (strpos($text, 'cash (kas besar)') !== false) {
            return $this->findAccount('Kas Besar');
        }
        return null;
    }

    /**
     * Pick specific Bank account by payment text. Prefers names containing BCA/BRI/MANDIRI/BNI.
     * Looks by exact account names like "Bank BCA" first, then by LIKE name contains.
     * Now supports QRIS, EDC, and Giro sub-accounts.
     */
    private function resolveBankAccountByText(?string $caraBayar, ?string $pembayaran): ?ChartOfAccount
    {
        $text = strtoupper(trim(($caraBayar ?? '').' '.($pembayaran ?? '')));
        if ($text === '') { return null; }
        
        // First, try to find specific sub-accounts for QRIS, EDC, Giro
        if (strpos($text, 'QRIS') !== false) {
            if (strpos($text, 'BCA') !== false) {
                $acc = $this->findAccount('Bank BCA - QRIS');
                if ($acc) return $acc;
            }
            if (strpos($text, 'BRI') !== false) {
                $acc = $this->findAccount('Bank BRI - QRIS');
                if ($acc) return $acc;
            }
            if (strpos($text, 'MANDIRI') !== false) {
                $acc = $this->findAccount('Bank Mandiri - QRIS');
                if ($acc) return $acc;
            }
            if (strpos($text, 'BNI') !== false) {
                $acc = $this->findAccount('Bank BNI - QRIS');
                if ($acc) return $acc;
            }
        }
        
        if (strpos($text, 'EDC') !== false) {
            if (strpos($text, 'BCA') !== false) {
                $acc = $this->findAccount('Bank BCA - EDC');
                if ($acc) return $acc;
            }
            if (strpos($text, 'BRI') !== false) {
                $acc = $this->findAccount('Bank BRI - EDC');
                if ($acc) return $acc;
            }
            if (strpos($text, 'MANDIRI') !== false) {
                $acc = $this->findAccount('Bank Mandiri - EDC');
                if ($acc) return $acc;
            }
            if (strpos($text, 'BNI') !== false) {
                $acc = $this->findAccount('Bank BNI - EDC');
                if ($acc) return $acc;
            }
        }
        
        if (strpos($text, 'GIRO') !== false) {
            if (strpos($text, 'BCA') !== false) {
                $acc = $this->findAccount('Bank BCA - Giro');
                if ($acc) return $acc;
            }
            if (strpos($text, 'BRI') !== false) {
                $acc = $this->findAccount('Bank BRI - Giro');
                if ($acc) return $acc;
            }
            if (strpos($text, 'MANDIRI') !== false) {
                $acc = $this->findAccount('Bank Mandiri - Giro');
                if ($acc) return $acc;
            }
            if (strpos($text, 'BNI') !== false) {
                $acc = $this->findAccount('Bank BNI - Giro');
                if ($acc) return $acc;
            }
        }
        
        // Fallback to general bank accounts
        $banks = [
            'BCA' => ['Bank BCA','BCA'],
            'BRI' => ['Bank BRI','BRI'],
            'MANDIRI' => ['Bank Mandiri','MANDIRI'],
            'BNI' => ['Bank BNI','BNI'],
        ];
        foreach ($banks as $key => $candidates) {
            if (strpos($text, $key) !== false) {
                // Try exact common names first
                foreach ($candidates as $name) {
                    $acc = $this->findAccount($name);
                    if ($acc) return $acc;
                }
                // Then LIKE search
                foreach ($candidates as $name) {
                    $acc = ChartOfAccount::where('name', 'like', "%{$name}%")->first();
                    if ($acc) return $acc;
                }
            }
        }
        return null;
    }
}


