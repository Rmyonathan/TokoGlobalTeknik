<?php

namespace App\Http\Controllers;
use App\Http\Controllers\BookingsController;

use App\Models\Kas;
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

        $pembelianQuery = Pembelian::all();
        $penjualanQuery = Transaksi::all();

        $array_pembelian = [];
        $array_penjualan = [];

        $kasQuery = Kas::all();
        $array_kas = [];

        // Handle Pembelian
        foreach ($pembelianQuery as $pembelian) {
            $items = PembelianItem::where('nota', $pembelian->nota)->get();
            $items_list = [];

            foreach ($items as $item) {
                $items_list[] = $item->kode_barang . ' x ' . $item->qty;
            }

            $array_pembelian[] = [
                'Name' => $pembelian->nota,
                'Deskripsi' => implode(', ', $items_list),
                'Grand total' => $pembelian->grand_total,
                'Date' => $pembelian->created_at,
                'Type' => 'Debit'
            ];
        }

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

        foreach ($kasQuery as $kas) {
            $array_kas[] = [
                'Name' => $kas->name,
                'Deskripsi' => $kas->description,
                'Grand total' => $kas->qty,
                'Date' => $kas->created_at,
                'Type' => $kas->type
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

        // // Get mutations for the filtered products
        // $mutations = collect([]);
        // $openingBalance = 0;
        // $selectedStock = null;

        // // Check if we should show mutations (either a single result or user clicked on an item)
        // $selectedKodeBarang = $request->input('selected_kode_barang');

        // if ($selectedKodeBarang) {
        //     // User specifically selected an item to view mutations for
        //     $selectedStock = $stocks->where('kode_barang', $selectedKodeBarang)->first();
        // } elseif ($stocks->count() == 1) {
        //     // Only one stock item found in search, automatically show its mutations
        //     $selectedStock = $stocks->first();
        // }

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

        //     $mutations = $mutationsQuery->get();
        // }

        return view('viewKas', compact('gabungan', 'value', 'tanggal_awal', 'tanggal_akhir'));
    }


   
}
