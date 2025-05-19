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

        // $pembelianQuery = Pembelian::all();
        $penjualanQuery = Transaksi::whereIn('cara_bayar', $tunaiCaraBayars)->get();

        $array_pembelian = [];
        $array_penjualan = [];

        $kasQuery = Kas::where('is_canceled', false)->get();
        $array_kas = [];

        // // Handle Pembelian
        // foreach ($pembelianQuery as $pembelian) {
        //     $items = PembelianItem::where('nota', $pembelian->nota)->get();
        //     $items_list = [];

        //     foreach ($items as $item) {
        //         $items_list[] = $item->kode_barang . ' x ' . $item->qty;
        //     }

        //     $array_pembelian[] = [
        //         'Name' => $pembelian->nota,
        //         'Deskripsi' => implode(', ', $items_list),
        //         'Grand total' => $pembelian->grand_total,
        //         'Date' => $pembelian->created_at,
        //         'Type' => 'Debit'
        //     ];
        // }

        // Handle Penjualan
        foreach ($penjualanQuery as $penjualan) {
            $items = TransaksiItem::where('no_transaksi', $penjualan->no_transaksi)->get();
            $items_list = [];

            foreach ($items as $item) {
                $items_list[] = $item->kode_barang . ' x ' . $item->qty;
            }

            $array_penjualan[] = [
                'Name' => $penjualan->no_transaksi,
                'Deskripsi' => implode(', ', $items_list),
                'Grand total' => $penjualan->grand_total,
                'Date' => $penjualan->created_at,
                'Type' => 'Kredit'
            ];
        }
        
        // Handle manual Kas entries - include ID for delete functionality
        foreach ($kasQuery as $kas) {
            $array_kas[] = [
                'id' => $kas->id, // Add the ID field for reference
                'Name' => $kas->name,
                'Deskripsi' => $kas->description,
                'Grand total' => $kas->qty,
                'Date' => $kas->created_at,
                'Type' => $kas->type,
                'is_manual' => $kas->is_manual // Add the is_manual flag
            ];
        }

        // Gabungkan dan urutkan berdasarkan tanggal
        $gabungan = array_merge($array_pembelian, $array_penjualan, $array_kas);
        usort($gabungan, function ($a, $b) {
            return strtotime($a['Date']) <=> strtotime($b['Date']);
        });

        $saldo = 0;

        foreach ($gabungan as $key => $row) {
            if ($row['Type'] == 'Kredit') {
                $saldo += $row['Grand total'];
            } elseif ($row['Type'] == 'Debit') {
                $saldo -= $row['Grand total'];
            }

            $gabungan[$key]['Saldo'] = $saldo;
        }

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