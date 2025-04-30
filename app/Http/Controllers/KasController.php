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

        // Gabungkan dan urutkan berdasarkan tanggal
        $gabungan = array_merge($array_pembelian, $array_penjualan);
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


    public function hutangLunas()
    {
    // Retrieve paid debts (lunas) with pagination
    $lunasTransactions = Kas::where('type', 'Hutang')
                            ->where('description', 'Lunas')
                            ->orderBy('created_at', 'desc')
                            ->paginate(10); // Paginate results

    return view('hutanglunas', [
        'lunasTransactions' => $lunasTransactions, // Send paginated results to the view
    ]);
    }

    public function hutangBelumLunas()
    {
    // Retrieve paid debts (lunas) with pagination
    $BelumlunasTransactions = Kas::where('type', 'Hutang')
                            ->where('description', 'Belum Lunas')
                            ->orderBy('created_at', 'desc')
                            ->paginate(10); // Paginate results

    return view('hutangbelumlunas', [
        'BelumlunasTransactions' => $BelumlunasTransactions, // Send paginated results to the view
    ]);
    }


    public function viewDebt(Request $request)
    {
        // Start with a base query for all transactions
        $baseQuery = Kas::query();

        // Apply name search filter across all transaction types if provided
        if ($request->filled('searchN')) {
            $searchN = $request->input('searchN');
            $baseQuery->where('name', 'like', '%' . $searchN . '%');
        }

        // Get the names that match the search criteria
        $matchingNames = $baseQuery->pluck('name')->unique();

        // Now create the final query that only shows Hutang type
        $query = Kas::where('type', 'Hutang');

        // If a search was performed, restrict to only the matching names
        if ($request->filled('searchN')) {
            $query->whereIn('name', $matchingNames);
        }

        // Get the paginated results
        $cashTransactions = $query->orderBy('created_at', 'desc')->paginate(10);

        // Retrieve the current balance
        $hutang = Saldo::find(1);

        // Calculate debit and credit totals
        $debitTotal = Kas::whereIn('type', ['Debit', 'Bonus'])->sum('transaction');
        $kreditTotal = Kas::where('type', 'Kredit')->sum('transaction');

        // Calculate grand total as debit - credit
        $grandTotal = $debitTotal - $kreditTotal;

        // Return the view with the necessary data
        return view('listutang', [
            "cashTransactions" => $cashTransactions,
            "saldo" => $hutang,
            "grandTotal" => $grandTotal,
            "debitTotal" => $debitTotal,
            "kreditTotal" => $kreditTotal,
        ]);
    }

    public function viewSlide(Request $request)
    {
        $saldo = Saldo::find(1);

        // Build the base query for filtering
        $query = Kas::query();

        // Apply filters
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('created_at', [$request->start_date, $request->end_date]);
        }

        if ($request->filled('searchN')) {
            $searchN = $request->input('searchN');
            $query->where('name', 'like', '%' . $searchN . '%');
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('description', 'like', '%' . $search . '%');
        }

        // Get all filtered records (without pagination) for total calculation
        $debitTotal = (clone $query)->whereIn('type', ['Debit', 'Bonus'])->sum('transaction');
        $kreditTotal = (clone $query)->where('type', 'Kredit')->sum('transaction');

        // Calculate grand total as debit - credit for filtered records
        $grandTotal = $debitTotal - $kreditTotal;

        // Get paginated records for display
        $cashTransactions = $query->orderBy('created_at', 'desc')->paginate(10);

        return view('viewkas', [
            "cashTransactions" => $cashTransactions,
            "saldo" => $saldo,
            "grandTotal" => $grandTotal,
            "debitTotal" => $debitTotal,
            "kreditTotal" => $kreditTotal,
        ]);
    }


    public function addTransaction(Request $request)
    {
        $newKas = $request->validate([
            'name' => 'required|max:25|regex:/^[\w\s-]*$/',
            'description' => 'nullable|string', // Allows null and must be a string
            'qty' => 'nullable|integer|min:0', // Must be an integer, can be null
            'transaction' => 'nullable|integer', // Must be an integer, can be null
            'type' => 'required'
        ]);

        $saldo = Saldo::find(1);

        $logKas = Items::where('name', $newKas['name'])->first();
        if ($newKas['type'] === 'Debit'|| $newKas['type'] === 'Bonus'){
            $saldo->saldo += $newKas['transaction'];

            if ($logKas){
                $logKas->stocks += $newKas['qty'];
                $newKas['transaction'] = $logKas->price * $newKas['qty'];
                $logKas->save();

                $saldo->saldo += $newKas['transaction'];
            }
        }
        else if ($newKas['type'] === 'Hutang'){
            // QTY = Hutang Awal
            // Deskripsi = Status
            $kasHutang = $newKas;
            $kasHutang['qty'] = 1;
            $kasHutang['type'] = 'Kredit';
            $kasHutang['saldo'] = $saldo->saldo - $newKas['transaction'];
            Kas::create($kasHutang);

            $saldo->saldo -= $newKas['transaction'];
            $saldo->hutang += $newKas['transaction'];
            $newKas['qty'] = $newKas['transaction'];
            $newKas['description'] = 'Belum Lunas';
        }
        else {
            $saldo->saldo -= $newKas['transaction'];

            if ($logKas){
                $logKas->stocks -= $newKas['qty'];
                $newKas['transaction'] = $logKas->price * $newKas['qty'];
                $logKas->save();

                $saldo->saldo -= $newKas['transaction'];
            }
        }

        $saldo->save();

        $newKas['saldo'] = $saldo->saldo;
        Kas::create($newKas);

        return redirect('/viewKas')->with('success', 'Item added successfully!');
    }

    public function edit_kas(Request $request)
    {
        $kas = Kas::find($request->kas_id);
        $hutang = $request->hutang;

        if($request->booking_id){
            $booking = Bookings::find($request->booking_id);
            return view('edit_kas', [
                "kas" => $kas,
                "booking" => $booking
            ]);
        }

        return view('edit_kas', [
            "kas" => $kas,
            "hutang" => $hutang
        ]);
    }

    public function update_kas(Request $request)
    {
        $newItem = $request->validate([
            'name' => 'required|max:25|regex:/^[\w\s-]*$/', // Menyesuaikan tabel 'kas'
            'description' => 'required|max:255',
            'transaction' => 'required|numeric|min:0',
            'qty' => 'required|numeric|min:0',
            'type' => 'required'
        ]);

        // Insert ke dalam tabel Kas
        if ($newItem['type'] != 'Hutang'){
            $newItem['transaction'] = $newItem['transaction'] * $newItem['qty'];
        }

        $saldo = Saldo::find(1);

        if($newItem['type'] === 'Kredit'){
            $saldo->saldo -= $newItem['transaction'];
            $saldo->save();
        }
        else {
            $saldo->saldo += $newItem['transaction'];
            $saldo->save();
        }

        $newItem['saldo'] = $saldo->saldo;

        $kas = Kas::create($newItem);

        // Pastikan booking_id ada
        $org_kas = Kas::find($request->transaction_id);
        if (!$org_kas) {
            return redirect()->back()->with('error', 'Transaction not found');
        }

        $xitems = XItems::where('kas_id', $org_kas->id)->first();

        if ($xitems) {
            $booking = Bookings::find($xitems->booking_id);
            if (!$booking) {
                return back()->withErrors(['error' => 'Booking not found']);
            }

            // Jika pricelist_id ada (sekarang berdasarkan ID dari Kas yang baru dibuat)
            if ($kas->id) {
                // Cek apakah item sudah ada berdasarkan kas_id & booking_id
                $existingXItem = XItems::where('kas_id', $kas->id)
                                    ->where('booking_id', $booking->id)
                                    ->first();

                if ($existingXItem) {
                    // Jika sudah ada, tambahkan quantity
                    $existingXItem->qty += $kas->qty;
                    $existingXItem->save();
                } else {
                    // Jika belum ada, buat entri baru
                    XItems::create([
                        'booking_id' => $booking->id,
                        'kas_id' => $kas->id, // Menggunakan ID dari Kas
                        'qty' => $kas->qty,
                    ]);
                }

                // Update total_amount di booking berdasarkan harga dari Kas yang baru dibuat
                $booking->total_modal += $kas->transaction;
                $booking->untung -= $kas->transaction;

                $booking->total_modal -= $org_kas->transaction;
                $booking->untung += $org_kas->transaction;
                $xitems->kas_id = $kas->id;
                $booking->save();
            }
        }

        $booking = Bookings::where('kas_id', $org_kas->id)->first();

        if ($booking){
            $booking->id = $kas->id;

            if(!$xitems){
                $booking->modal_beli = $kas->transaction;
                $booking->total_modal -= $org_kas->transaction;
                $booking->total_modal += $kas->transaction;
                $booking->untung = $booking->harga_jual - $kas->total_modal;
                $booking->save();
            }
        }

        $saldo = Saldo::find(1); // Pastikan ada model Saldo

        // $KasNames = Kas::all()->pluck('name');
        $logKas = Items::where('kas_id', $org_kas->id)->first();

        if($newItem['type'] === 'Kredit'){
            $saldo->saldo += $org_kas->transaction;
            $saldo->save();

            if ($logKas){
                $newLog['kas_id'] = $kas->id;
                $newLog['name'] = $kas->name;
                $newLog['description'] = $kas->description;
                $newLog['price'] = ($kas->transaction / $kas->qty);
                $newLog['stocks'] = $kas->qty;

                Items::create($newLog);
            }
        }
        elseif ($newItem['type'] === 'Hutang'){
            // $saldo->saldo += $org_kas->transaction;
            // $saldo->hutang += $kas->transaction;
            $saldo->hutang -= $kas->transaction;
            $saldo->save();
            $kas->transaction = $org_kas->transaction - $kas->transaction;
            if ($kas->transaction === 0){
                $kas->description = 'Lunas';
            }
            // $saldo->save();

            $newKas = $newItem;
            $newKas['description'] = 'Nyicil';
            $newKas['qty'] = 1;
            $newKas['type'] = 'Debit';
            $newKas['saldo'] = $saldo->saldo;
            Kas::create($newKas);
        }
        else {
            $saldo->saldo -= $org_kas->transaction;
            $saldo->save();

            // if ($logKas){
            //     $newLog['name'] = $kas->name;
            //     $newLog['description'] = $kas->description;
            //     $newLog['stocks'] = $kas->qty;
            //     $newLog['price'] = $kas->transaction / $kas->qty;
            //     $newLog['kas_id'] = $kas->id;

            //     Items::create($newLog);
            // }
        }

        $kas->saldo = $saldo->saldo;
        $kas->save();

        // Hapus transaksi kas

        $org_kas->delete();

        if ($request->booking_id){
            $bookingsController = new BookingsController();

            // $customData = [
            //     'booking_id' => $bookings->id,
            // ];

            // $modifiedRequest = $request->merge($customData);
            return $bookingsController->editbooking($request);
        }
        else {
            return redirect()->intended('/viewKas')->with('success', 'Transaction canceled successfully');
        }
    }

    public function delete_kas(Request $request){
        $kas = Kas::find($request->transaction_id);
        $kas->delete();
        return redirect()->intended('/viewKas');
    }

    public function cancel_kas(Request $request) {
        $kas = Kas::find($request->transaction_id);
        if (!$kas) {
            return redirect()->back()->with('error', 'Transaction not found');
        }

        $xitems = XItems::where('kas_id', $kas->id)->first();

        if ($xitems){
            $bookings = Bookings::find($xitems->booking_id);

            $bookings->total_modal -= $kas->transaction;
            $bookings->untung += $kas->transaction;
            $bookings->save();
            $xitems->delete();
        }

        $co = Bookings::where('kas_co', $kas->id)->first();

        if($co){
            if ($kas->id === $co->kas_co){
                $co->status = 'checkIn';
                $co->save();
            }
        }

        // Ambil saldo dan update
        $saldo = Saldo::find(1); // Pastikan ada model Saldo
        $KasNames = Kas::all()->pluck('name');
        // $logKas = Items::whereIn('name', $KasNames)->first();

        if($kas->type === 'Kredit'){
            $saldo->saldo += $kas->transaction;
            $saldo->save();

            // if ($logKas){
            //     $logKas->stocks += $kas->qty;
            //     $logKas->save();
            // }
        }
        elseif($kas->type === 'Hutang'){
            $saldo->saldo += $kas->transaction;
            $saldo->hutang -= $kas->transaction;
            $saldo->save();

            $kas->description = 'Lunas';
            $kas->save();
        }
        else {
            $saldo->saldo -= $kas->transaction;
            $saldo->save();

            // if ($logKas){
            //     $logKas->stocks -= $kas->qty;
            //     $logKas->save();
            // }
        }

        // Hapus transaksi kas
        if ($kas->type != 'Hutang'){
            $kas->delete();
        }

        if ($request->booking_id){
            $bookingsController = new BookingsController();

            // $customData = [
            //     'booking_id' => $bookings->id,
            // ];

            // $modifiedRequest = $request->merge($customData);
            return $bookingsController->editbooking($request);
        }
        else {
            return redirect()->intended('/viewKas')->with('success', 'Transaction canceled successfully');
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Kas $kas)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Kas $kas)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Kas $kas)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Kas $kas)
    {
        //
    }
}
