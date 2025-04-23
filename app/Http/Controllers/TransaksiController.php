<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Transaksi;
use App\Models\TransaksiItem;
use App\Models\Customer;
use App\Models\Panel;

class TransaksiController extends Controller
{
    /**
     * Display the sales transaction form.
     */
    public function penjualan()
    {
        // Ambil nomor transaksi terakhir
        $lastTransaction = Transaksi::orderBy('created_at', 'desc')->first();
    
        // Generate nomor transaksi baru
        if ($lastTransaction) {
            // Ambil angka terakhir dari no_transaksi
            $lastNumber = (int) substr($lastTransaction->no_transaksi, strrpos($lastTransaction->no_transaksi, '/') + 1);
            $newNumber = $lastNumber + 1;
        } else {
            // Jika belum ada transaksi, mulai dari 1
            $newNumber = 1;
        }
    
        // Format nomor transaksi baru
        $noTransaksi = 'KP/WS/' . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    
        return view('transaksi.penjualan', compact('noTransaksi'));
    }


    
    /**
     * Store a sales transaction.
     */
    public function store(Request $request)
    {
        $request->validate([
            'no_transaksi' => 'required|string|unique:transaksi,no_transaksi',
            'tanggal' => 'required|date',
            'kode_customer' => 'required|exists:customers,kode_customer',
            'sales' => 'required|exists:stok_owners,kode_stok_owner', // Validasi sales
            'subtotal' => 'required|numeric',
            'grand_total' => 'required|numeric',
            'items' => 'required|array',
            'items.*.kodeBarang' => 'required|exists:panels,id', // Validasi kode_barang
            'items.*.harga' => 'required|numeric',
            'items.*.qty' => 'required|numeric',
        ]);
        
        try {
            DB::beginTransaction();

            $ppn = str_replace(',', '.', $request->ppn);
            
            // Create transaction
            $transaksi = Transaksi::create([
                'no_transaksi' => $request->no_transaksi,
                'tanggal' => $request->tanggal,
                'kode_customer' => $request->kode_customer,
                'sales' => $request->sales,
                'lokasi' => $request->lokasi,
                'pembayaran' => $request->pembayaran,
                'cara_bayar' => $request->cara_bayar,
                'tanggal_jadi' => $request->tanggal_jadi,
                'subtotal' => $request->subtotal,
                'discount' => $request->discount ?? 0,
                'disc_rupiah' => $request->disc_rp ?? 0,
                'ppn' => $ppn,
                'dp' => $request->dp ?? 0,
                'grand_total' => $request->grand_total,
                'status' => 'baru',
            ]);
            
            // Create transaction items
            foreach ($request->items as $item) {
                TransaksiItem::create([
                    'no_transaksi' => $request->no_transaksi, // Gunakan no_transaksi sebagai foreign key
                    'kode_barang' => $item['kodeBarang'],
                    'nama_barang' => Panel::find($item['kodeBarang'])->name,
                    'keterangan' => $item['keterangan'] ?? null,
                    'harga' => $item['harga'],
                    'panjang' => $item['panjang'] ?? 0,
                    'lebar' => $item['lebar'] ?? 0,
                    'qty' => $item['qty'],
                    'diskon' => $item['diskon'] ?? 0,
                    'total' => $item['total'],
                ]);
            }
            
            DB::commit();

            return response()->json([
                'id' => $transaksi->id,
                'no_transaksi' => $transaksi->no_transaksi,
                'tanggal' => $transaksi->tanggal,
                'customer' => $transaksi->customer->nama ?? 'N/A',
                'grand_total' => $transaksi->grand_total,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Search for products
     */
    public function searchProducts(Request $request)
    {
        $keyword = $request->keyword;
        
        $products = DB::table('barang')
            ->where('kode_barang', 'like', "%{$keyword}%")
            ->orWhere('nama_barang', 'like', "%{$keyword}%")
            ->limit(10)
            ->get();
        
        return response()->json($products);
    }
    
    /**
     * Search for customers
     */
    public function searchCustomers(Request $request)
    {
        $keyword = $request->keyword;
        
        $customers = DB::table('customers')
            ->where('nama', 'like', "%{$keyword}%")
            ->orWhere('kode_customer', 'like', "%{$keyword}%")
            ->limit(10)
            ->get();
        
        return response()->json($customers);
    }
    
    /**
     * Create a new customer
     */
    public function createCustomer(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:100',
            'alamat' => 'nullable|string',
            'telepon' => 'nullable|string|max:20',
        ]);
        
        try {
            $customer = DB::table('customers')->insert([
                'nama' => $request->nama,
                'alamat' => $request->alamat,
                'telepon' => $request->telepon,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Customer berhasil ditambahkan',
                'data' => $customer
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get transaction data
     */
    public function getTransaction($id)
    {
        $transaction = Transaksi::with('items')->findOrFail($id);
        
        return response()->json($transaction);
    }

    /**
     * Get transaction data from customers
     */
    public function datapenjualanpercustomer(Request $request)
    {
        // Ambil daftar customer yang telah melakukan transaksi
        $customers = DB::table('transaksi')
            ->join('customers', 'transaksi.kode_customer', '=', 'customers.kode_customer')
            ->select('customers.kode_customer', 'customers.nama', DB::raw('COUNT(transaksi.id) as total_transaksi'))
            ->groupBy('customers.kode_customer', 'customers.nama')
            ->get();

        // Jika ada customer yang dipilih, ambil daftar transaksi
        $transaksi = [];
        if ($request->has('kode_customer')) {
            $transaksi = DB::table('transaksi')
                ->where('kode_customer', $request->kode_customer)
                ->get();
        }
            
        return view('transaksi.datapenjualanpercustomer', compact('customers', 'transaksi'));
    }

    /**
     * Show the invoice (nota) for a transaction
     */
    public function showNota($id)
    {
        $transaction = Transaksi::with('items', 'customer')->findOrFail($id);
        
        return view('transaksi.nota', compact('transaction'));
    }

    public function nota($no_transaksi)
    {
        $transaction = Transaksi::with('items', 'customer')->where('no_transaksi', $no_transaksi)->firstOrFail();
        return view('transaksi.nota', compact('transaction'));
    }

    public function listNota()
    {
        // Fetch all transactions (penjualan & pembelian)
        $transactions = Transaksi::with('items')->orderBy('created_at', 'desc')->get();

        return view('transaksi.lihat_nota', compact('transactions'));
    }

}