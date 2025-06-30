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
use Illuminate\Pagination\LengthAwarePaginator;
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

        return redirect()->route('kas.view')->with('success', 'Kas entry added successfully.');
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

        // --- Ambil semua data dari database (logika ini tetap sama) ---
        $tunaiCaraBayars = CaraBayar::where('metode', 'Tunai')->pluck('nama')->toArray();
        $allTransactions = Transaksi::whereIn('cara_bayar', $tunaiCaraBayars)->get();
        
        $array_penjualan = [];
        foreach ($allTransactions as $penjualan) {
            $items_list = TransaksiItem::where('no_transaksi', $penjualan->no_transaksi)->get()->map(fn($item) => $item->kode_barang . ' x ' . $item->qty)->implode(', ');
            $namePrefix = $penjualan->status === 'canceled' ? 'Batal Transaksi: ' : ($penjualan->is_edited ? '[EDITED] ' : '');
            $array_penjualan[] = [
                'Name' => $namePrefix . $penjualan->no_transaksi, 'Deskripsi' => $items_list, 'Grand total' => $penjualan->status === 'canceled' ? 0 : $penjualan->grand_total, 'Date' => $penjualan->created_at, 'Type' => $penjualan->status === 'canceled' ? 'Batal' : 'Kredit', 'is_transaction' => true, 'transaction_status' => $penjualan->status, 'is_edited' => $penjualan->is_edited, 'original_amount' => $penjualan->grand_total,
            ];
        }

        $kasQuery = Kas::orderBy('created_at', 'asc')->get();
        $array_kas = [];
        foreach ($kasQuery as $kas) {
            if (!$kas->is_manual && (strpos($kas->name, 'Batal Transaksi:') !== false || strpos($kas->name, 'Edit Transaksi:') !== false)) continue;
            $isKasCanceled = $kas->is_canceled;
            $array_kas[] = [
                'id' => $kas->id, 'Name' => $isKasCanceled ? '[DIBATALKAN] ' . $kas->name : $kas->name, 'Deskripsi' => $kas->description, 'Grand total' => $isKasCanceled ? 0 : $kas->qty, 'Date' => $kas->created_at, 'Type' => $isKasCanceled ? 'Batal' : $kas->type, 'is_manual' => $kas->is_manual, 'kas_type' => $kas->is_manual ? 'Manual' : 'Sistem', 'is_transaction' => false, 'is_kas_canceled' => $isKasCanceled, 'original_amount' => $kas->qty,
            ];
        }

        // 1. Gabungkan dan urutkan semua data secara kronologis
        $gabungan = array_merge($array_penjualan, $array_kas);
        usort($gabungan, fn($a, $b) => strtotime($a['Date']) <=> strtotime($b['Date']));

        // 2. Hitung saldo berjalan pada seluruh data
        $saldo = 0;
        foreach ($gabungan as $key => $row) {
            if ($row['Type'] !== 'Batal') {
                $saldo += ($row['Type'] == 'Kredit' ? $row['Grand total'] : -$row['Grand total']);
            }
            $gabungan[$key]['Saldo'] = $saldo;
        }

        // 3. Ambil saldo terakhir SEBELUM data difilter
        $saldoSaatIni = !empty($gabungan) ? end($gabungan)['Saldo'] : 0;
        
        // 4. Terapkan filter pada data
        $filtered_gabungan_collection = collect($gabungan)->when($value, function ($collection, $value) {
            return $collection->filter(fn($item) => stripos($item['Name'], $value) !== false || stripos($item['Deskripsi'], $value) !== false);
        })->when($tanggal_awal, function ($collection, $tanggal_awal) {
            return $collection->filter(fn($item) => \Carbon\Carbon::parse($item['Date'])->toDateString() >= $tanggal_awal);
        })->when($tanggal_akhir, function ($collection, $tanggal_akhir) {
            return $collection->filter(fn($item) => \Carbon\Carbon::parse($item['Date'])->toDateString() <= $tanggal_akhir);
        });
        
        $filtered_gabungan = $filtered_gabungan_collection->values()->all();
        
        // 5. Hitung total berdasarkan data yang SUDAH difilter
        $totalKredit = $filtered_gabungan_collection->where('Type', 'Kredit')->sum('Grand total');
        $totalDebit = $filtered_gabungan_collection->where('Type', 'Debit')->sum('Grand total');
        $totalTransaksi = count($filtered_gabungan);

        // 6. Balik urutan array agar data terbaru di atas
        $reversed_gabungan = array_reverse($filtered_gabungan);

        // 7. Buat objek paginator secara manual dari array
        $perPage = 15;
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $currentPageItems = array_slice($reversed_gabungan, ($currentPage - 1) * $perPage, $perPage);

        $paginated_results = new LengthAwarePaginator(
            $currentPageItems,
            count($reversed_gabungan),
            $perPage,
            $currentPage,
            // Penting: tambahkan path agar link paginasi berfungsi dengan benar
            ['path' => LengthAwarePaginator::resolveCurrentPath()]
        );

        // 8. Kirim objek paginator ke view
        return view('viewKas', [
            'gabungan' => $paginated_results,
            'saldoSaatIni' => $saldoSaatIni, 
            'totalKredit' => $totalKredit,
            'totalDebit' => $totalDebit,
            'totalTransaksi' => $totalTransaksi,
            'value' => $value, 
            'tanggal_awal' => $tanggal_awal, 
            'tanggal_akhir' => $tanggal_akhir
        ]);
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