<?php

namespace App\Http\Controllers;
use App\Http\Controllers\BookingsController;

use App\Models\Kas;
use App\Models\CaraBayar;
use App\Models\Saldo;
use App\Models\Bookings;
use App\Models\XItems;
use App\Models\Items;
use App\Models\Pembelian;
use App\Models\Transaksi;
use App\Models\PembelianItem;
use App\Models\TransaksiItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class KasController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function create()
    {
        return view('addKas');
    }

    // Store kas entry
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'qty' => 'required|numeric',
            'type' => 'required|in:Kredit,Debit', // Only these two allowed
        ]);

        // Determine saldo based on type
        $lastKas = Kas::latest()->first();
        $previousSaldo = $lastKas ? $lastKas->saldo : 0;
        $newSaldo = $validated['type'] == 'Kredit'
            ? $previousSaldo + $validated['qty']
            : $previousSaldo - $validated['qty'];

        $kas = Kas::create([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'qty' => $validated['qty'],
            'type' => $validated['type'],
            'saldo' => $newSaldo,
            'is_manual' => true, // Mark this as a manually created entry
        ]);

        return redirect()->route('kas.create')->with('success', 'Kas entry added successfully.');
    }

    //BUAT ADD TRANSACTION
    public function index(Request $request)
    {
        $hutang = $request->hutang;

        return view('addtransaction', [
            "hutang" => $hutang,
        ]);
    }

    public function viewKas(Request $request)
    {
        $value = $request->input('value');
        $tanggal_awal = $request->input('tanggal_awal');
        $tanggal_akhir = $request->input('tanggal_akhir');

        $tunaiCaraBayars = CaraBayar::where('metode', 'Tunai')->pluck('nama')->toArray();

        // Get active transactions (not canceled) for initial display
        $penjualanQuery = Transaksi::whereIn('cara_bayar', $tunaiCaraBayars)
            ->where('status', '!=', 'canceled')
            ->get();

        // Get ALL transactions including canceled ones for reference
        $allTransactions = Transaksi::whereIn('cara_bayar', $tunaiCaraBayars)->get();
        
        // Create a lookup of canceled transactions
        $canceledTransactions = [];
        foreach ($allTransactions as $transaction) {
            if ($transaction->status === 'canceled') {
                $canceledTransactions[$transaction->no_transaksi] = true;
            }
        }

        $array_pembelian = [];
        $array_penjualan = [];

        // Get all kas entries
        $kasQuery = Kas::orderBy('created_at', 'asc')->get();
        $array_kas = [];

        // Handle Penjualan - include both active and canceled transactions but mark canceled ones
        foreach ($allTransactions as $penjualan) {
            $items = TransaksiItem::where('no_transaksi', $penjualan->no_transaksi)->get();
            $items_list = [];

            foreach ($items as $item) {
                $items_list[] = $item->kode_barang . ' x ' . $item->qty;
            }

            // Add status indicator
            $namePrefix = '';
            if ($penjualan->status === 'canceled') {
                $namePrefix = 'Batal Transaksi: ';
            } elseif ($penjualan->is_edited) {
                $namePrefix = '[EDITED] ';
            }

            $array_penjualan[] = [
                'Name' => $namePrefix . $penjualan->no_transaksi,
                'Deskripsi' => implode(', ', $items_list),
                'Grand total' => $penjualan->status === 'canceled' ? 0 : $penjualan->grand_total, // Show 0 for canceled
                'Date' => $penjualan->created_at,
                'Type' => $penjualan->status === 'canceled' ? 'Batal' : 'Kredit', // Special type for canceled
                'is_transaction' => true,
                'transaction_status' => $penjualan->status,
                'is_edited' => $penjualan->is_edited,
                'original_amount' => $penjualan->grand_total, // Keep for reference
            ];
        }
        
        // Handle Kas entries but skip cancellation entries - we'll use the transactions directly
        foreach ($kasQuery as $kas) {
            // Skip any automatic kas entries created due to cancellations
            if (!$kas->is_manual && strpos($kas->name, 'Batal Transaksi:') !== false) {
                continue;
            }
            
            // Skip any differential entries created due to edits
            if (!$kas->is_manual && strpos($kas->name, 'Edit Transaksi:') !== false) {
                continue;
            }
            
            // Include other entries
            $kasType = 'Manual';
            $isKasCanceled = $kas->is_canceled;
            
            if (!$kas->is_manual) {
                if (strpos($kas->name, 'Edit Transaksi:') !== false) {
                    $kasType = 'Edit Transaksi';
                } else {
                    $kasType = 'Sistem';
                }
            }

            // Modify display values for canceled entries
            $displayName = $kas->name;
            $displayAmount = $kas->qty;
            $displayType = $kas->type;
            
            if ($isKasCanceled) {
                $displayName = '[DIBATALKAN] ' . $kas->name;
                $displayAmount = 0; // Show 0 for canceled entries
                $displayType = 'Batal';
            }

            $array_kas[] = [
                'id' => $kas->id,
                'Name' => $displayName,
                'Deskripsi' => $kas->description,
                'Grand total' => $displayAmount,
                'Date' => $kas->created_at,
                'Type' => $displayType,
                'is_manual' => $kas->is_manual,
                'kas_type' => $kasType,
                'is_transaction' => false,
                'is_kas_canceled' => $isKasCanceled,
                'original_amount' => $kas->qty, // Keep for reference
            ];
        }

        // Combine and sort by date
        $gabungan = array_merge($array_pembelian, $array_penjualan, $array_kas);
        usort($gabungan, function ($a, $b) {
            return strtotime($a['Date']) <=> strtotime($b['Date']);
        });

        // Calculate running saldo - handle cancellations as zero effect
        $saldo = 0;
        foreach ($gabungan as $key => $row) {
            // Skip canceled entries in calculation or count them as zero
            if ($row['Type'] !== 'Batal') {
                if ($row['Type'] == 'Kredit') {
                    $saldo += $row['Grand total'];
                } elseif ($row['Type'] == 'Debit') {
                    $saldo -= $row['Grand total'];
                }
            }
            
            // Store the running saldo for this entry
            $gabungan[$key]['Saldo'] = $saldo;
        }

        // Apply filters
        if ($value) {
            $gabungan = collect($gabungan)->filter(function ($item) use ($value) {
                return stripos($item['Name'], $value) !== false;
            })->values()->all();
        }

        // Apply date filters if provided
        $gabungan = collect($gabungan);
        if ($tanggal_awal) {
            $gabungan = $gabungan->filter(function ($item) use ($tanggal_awal) {
                return \Carbon\Carbon::parse($item['Date'])->toDateString() >= $tanggal_awal;
            });
        }

        if ($tanggal_akhir) {
            $gabungan = $gabungan->filter(function ($item) use ($tanggal_akhir) {
                return \Carbon\Carbon::parse($item['Date'])->toDateString() <= $tanggal_akhir;
            });
        }

        $gabungan = $gabungan->values()->all();

        // Calculate summary totals correctly
        $totalKredit = 0;
        $totalDebit = 0;
        $totalTransaksi = count($allTransactions);
        
        foreach ($gabungan as $item) {
            if ($item['Type'] === 'Kredit') {
                $totalKredit += $item['Grand total'];
            } elseif ($item['Type'] === 'Debit') {
                $totalDebit += $item['Grand total'];
            }
            // Batal type entries are excluded from totals
        }
        
        $saldoSaatIni = $totalKredit - $totalDebit;

        return view('viewKas', compact('gabungan', 'value', 'tanggal_awal', 'tanggal_akhir', 
            'totalKredit', 'totalDebit', 'saldoSaatIni', 'totalTransaksi'));
    }

    // DELETE KAS
    public function delete_kas(Request $request)
    {
        // Find the Kas entry
        $kasId = $request->kas_id;
        $kas = Kas::findOrFail($kasId);
        
        // Security check: Only allow deletion of manually created entries
        if (!$kas->is_manual) {
            return redirect('/viewKas')->with('error', 'Hanya Kas yang dibuat manual yang dapat dihapus.');
        }
        
        // Store the deleted entry's timestamp for comparison
        $deletedTimestamp = $kas->created_at;
        
        // Delete the entry
        $kas->delete();
        
        // Get all subsequent Kas entries and recalculate their saldo
        $subsequentEntries = Kas::where('created_at', '>', $deletedTimestamp)
                                ->orderBy('created_at', 'asc')
                                ->get();
        
        if ($subsequentEntries->count() > 0) {
            // Get the entry immediately before the first subsequent entry
            $previousEntry = Kas::where('created_at', '<', $subsequentEntries->first()->created_at)
                                ->orderBy('created_at', 'desc')
                                ->first();
            
            $currentSaldo = $previousEntry ? $previousEntry->saldo : 0;
            
            // Update each subsequent entry with the new saldo
            foreach ($subsequentEntries as $entry) {
                if ($entry->type == 'Kredit') {
                    $currentSaldo += $entry->qty;
                } else {
                    $currentSaldo -= $entry->qty;
                }
                
                $entry->saldo = $currentSaldo;
                $entry->save();
            }
        }
        
        return redirect('/viewKas')->with('success', 'Kas berhasil dihapus dan saldo telah diperbarui.');
    }

    // Cancel kas
    public function cancel_kas(Request $request)
    {
        // Find the Kas entry
        $kasId = $request->kas_id;
        $kas = Kas::findOrFail($kasId);
        
        // Security check: Only allow cancellation of manually created entries
        if (!$kas->is_manual) {
            return redirect('/viewKas')->with('error', 'Hanya Kas yang dibuat manual yang dapat dibatalkan.');
        }
        
        // Store the canceled entry's timestamp for comparison
        $canceledTimestamp = $kas->created_at;
        
        // Instead of deleting, mark as canceled
        $kas->is_canceled = true;
        $kas->save();
        
        // Get all subsequent Kas entries and recalculate their saldo
        $subsequentEntries = Kas::where('created_at', '>', $canceledTimestamp)
                                ->where('is_canceled', false) // Only include active entries
                                ->orderBy('created_at', 'asc')
                                ->get();
        
        if ($subsequentEntries->count() > 0) {
            // Get the entry immediately before the first subsequent entry
            $previousEntry = Kas::where('created_at', '<', $subsequentEntries->first()->created_at)
                                ->where('is_canceled', false) // Only include active entries
                                ->orderBy('created_at', 'desc')
                                ->first();
            
            $currentSaldo = $previousEntry ? $previousEntry->saldo : 0;
            
            // Update each subsequent entry with the new saldo
            foreach ($subsequentEntries as $entry) {
                if ($entry->type == 'Kredit') {
                    $currentSaldo += $entry->qty;
                } else {
                    $currentSaldo -= $entry->qty;
                }
                
                $entry->saldo = $currentSaldo;
                $entry->save();
            }
        }
        
        return redirect('/viewKas')->with('success', 'Kas berhasil dibatalkan dan saldo telah diperbarui.');
    }
    
    /**
     * Add method for editing cash transactions
     * This method is called by the TransaksiController when a transaction is edited
     */
    public function updateCashTransaction($transactionNumber, $oldAmount, $newAmount, $description, $userName = null)
    {
        try {
            DB::beginTransaction();
            
            // Find all Kas entries related to this transaction
            $relatedEntries = Kas::where('name', 'like', "%{$transactionNumber}%")
                ->orderBy('created_at', 'asc')
                ->get();
                
            if ($relatedEntries->isEmpty()) {
                // No related entries found, this is unusual
                Log::warning("No Kas entries found for transaction {$transactionNumber} when trying to update");
                DB::rollBack();
                return false;
            }
            
            // Get the original entry (should be the first one)
            $originalEntry = $relatedEntries->first();
            
            // Delete any difference entries that might have been created before
            foreach ($relatedEntries as $entry) {
                if ($entry->id !== $originalEntry->id && strpos($entry->name, 'Edit Transaksi:') !== false) {
                    $entry->delete();
                }
            }
            
            // Update the original entry with the new amount
            $originalEntry->qty = $newAmount;
            $originalEntry->description = $description;
            $originalEntry->save();
            
            // Get all entries after this one
            $allKas = Kas::where('created_at', '>=', $originalEntry->created_at)
                ->orderBy('created_at', 'asc')
                ->get();
                
            // Recalculate saldo for all entries
            $this->recalculateSaldo($allKas);
            
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error updating cash transaction: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Recalculate saldo for all entries after a change
     */
    private function recalculateSaldo($entries)
    {
        // If no entries provided, get all entries
        if (empty($entries)) {
            $entries = Kas::orderBy('created_at', 'asc')->get();
        }
        
        $currentSaldo = 0;
        foreach ($entries as $entry) {
            // Skip canceled entries
            if (isset($entry->is_canceled) && $entry->is_canceled) {
                continue;
            }
            
            if ($entry->type == 'Kredit') {
                $currentSaldo += $entry->qty;
            } else {
                $currentSaldo -= $entry->qty;
            }
            
            $entry->saldo = $currentSaldo;
            $entry->save();
        }
    }
    
    /**
     * Method to be called from TransaksiController to properly handle edited transactions
     */
    public function handleEditedTransaction($noTransaksi, $originalGrandTotal, $newGrandTotal, $reason, $editor)
    {
        DB::beginTransaction();
        try {
            // Find all Kas entries related to this transaction (both original and any diff entries)
            $relatedEntries = Kas::where('name', 'like', "%{$noTransaksi}%")
                ->orderBy('created_at', 'asc')
                ->get();
                
            // Find the main entry for this transaction
            $mainEntry = null;
            $diffEntries = [];
            
            foreach ($relatedEntries as $entry) {
                if (strpos($entry->name, 'Edit Transaksi:') === false && strpos($entry->name, 'Batal Transaksi:') === false) {
                    // This is likely the main entry
                    $mainEntry = $entry;
                } else {
                    // This is a diff entry from a previous edit
                    $diffEntries[] = $entry;
                }
            }
            
            if ($mainEntry) {
                // Update the main entry's amount
                $mainEntry->qty = $newGrandTotal;
                $mainEntry->description = $mainEntry->description . " (Edited: {$reason})";
                $mainEntry->save();
                
                // Delete all diff entries - we don't need them anymore
                foreach ($diffEntries as $entry) {
                    $entry->delete();
                }
                
                // Recalculate all saldo values
                $this->recalculateAllSaldo();
                
                DB::commit();
                return true;
            } else {
                Log::error("Main entry not found for transaction {$noTransaksi}");
                DB::rollBack();
                return false;
            }
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error in handleEditedTransaction: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Recalculate all saldo values from the beginning
     */
    private function recalculateAllSaldo()
    {
        $allEntries = Kas::where('is_canceled', false)
            ->orderBy('created_at', 'asc')
            ->get();
            
        $currentSaldo = 0;
        foreach ($allEntries as $entry) {
            if ($entry->type == 'Kredit') {
                $currentSaldo += $entry->qty;
            } else {
                $currentSaldo -= $entry->qty;
            }
            
            $entry->saldo = $currentSaldo;
            $entry->save();
        }
    }
}