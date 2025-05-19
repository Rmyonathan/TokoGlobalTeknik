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

        $penjualanQuery = Transaksi::whereIn('cara_bayar', $tunaiCaraBayars)
            ->where('status', '!=', 'canceled') // Exclude canceled transactions from original list
            ->get();

        $array_pembelian = [];
        $array_penjualan = [];

        // Get ALL kas entries including canceled ones - we want to show them with badges
        $kasQuery = Kas::orderBy('created_at', 'asc')->get(); // Remove the is_canceled filter
        $array_kas = [];

        // Handle Penjualan - only non-canceled cash transactions
        foreach ($penjualanQuery as $penjualan) {
            $items = TransaksiItem::where('no_transaksi', $penjualan->no_transaksi)->get();
            $items_list = [];

            foreach ($items as $item) {
                $items_list[] = $item->kode_barang . ' x ' . $item->qty;
            }

            // Add status indicator for edited transactions
            $namePrefix = '';
            if ($penjualan->is_edited) {
                $namePrefix = '[EDITED] ';
            }

            $array_penjualan[] = [
                'Name' => $namePrefix . $penjualan->no_transaksi,
                'Deskripsi' => implode(', ', $items_list),
                'Grand total' => $penjualan->grand_total,
                'Date' => $penjualan->created_at,
                'Type' => 'Kredit',
                'is_transaction' => true,
                'transaction_status' => $penjualan->status,
                'is_edited' => $penjualan->is_edited,
            ];
        }
        
        // Handle manual Kas entries and automatic entries from cancellations/edits
        foreach ($kasQuery as $kas) {
            // Skip canceled manual entries unless we want to show them
            if ($kas->is_canceled && $kas->is_manual) {
                continue; // Skip canceled manual entries
            }
            
            // Determine the type of kas entry
            $kasType = 'Manual'; // Default for manually created entries
            $isKasCanceled = $kas->is_canceled;
            
            if (!$kas->is_manual) {
                if (strpos($kas->name, 'Batal Transaksi:') !== false) {
                    $kasType = 'Pembatalan';
                } elseif (strpos($kas->name, 'Edit Transaksi:') !== false) {
                    $kasType = 'Edit Transaksi';
                } else {
                    $kasType = 'Sistem';
                }
            }

            // Modify the name to show canceled status
            $displayName = $kas->name;
            if ($isKasCanceled) {
                $displayName = '[DIBATALKAN] ' . $kas->name;
            }

            $array_kas[] = [
                'id' => $kas->id,
                'Name' => $displayName,
                'Deskripsi' => $kas->description,
                'Grand total' => $kas->qty,
                'Date' => $kas->created_at,
                'Type' => $kas->type,
                'is_manual' => $kas->is_manual,
                'kas_type' => $kasType,
                'is_transaction' => false,
                'is_kas_canceled' => $isKasCanceled, // Add this flag
            ];
        }

        // Combine and sort by date
        $gabungan = array_merge($array_pembelian, $array_penjualan, $array_kas);
        usort($gabungan, function ($a, $b) {
            return strtotime($a['Date']) <=> strtotime($b['Date']);
        });

        // Calculate running saldo - but exclude canceled entries from calculation
        $saldo = 0;
        foreach ($gabungan as $key => $row) {
            // Only include in saldo calculation if not canceled
            if (!isset($row['is_kas_canceled']) || !$row['is_kas_canceled']) {
                if ($row['Type'] == 'Kredit') {
                    $saldo += $row['Grand total'];
                } elseif ($row['Type'] == 'Debit') {
                    $saldo -= $row['Grand total'];
                }
            }
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

        return view('viewKas', compact('gabungan', 'value', 'tanggal_awal', 'tanggal_akhir'));
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
}