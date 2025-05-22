<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\Paginator;
use App\Models\Transaksi;
use App\Models\TransaksiItem;
use App\Models\Customer;
use App\Models\Panel;
use App\Models\SuratJalanItem;
use App\Models\Kas;
use App\Models\CaraBayar;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class TransaksiController extends Controller
{
    protected $stockController;
    protected $panelController;

    public function __construct(StockController $stockController, PanelController $panelController){
        $this->stockController = $stockController;
        $this->panelController = $panelController;
    }

    // Helper function to check if payment method is cash
    private function isCashPayment($caraBayar)
    {
        $tunaiCaraBayars = CaraBayar::where('metode', 'Tunai')->pluck('nama')->toArray();
        return in_array($caraBayar, $tunaiCaraBayars);
    }

    // Helper function to calculate and adjust kas saldo
    private function adjustKasSaldo()
    {
        // Get all kas entries ordered by creation date
        $allKas = Kas::orderBy('created_at', 'asc')->get();
        
        $saldo = 0;
        foreach ($allKas as $kas) {
            if ($kas->type == 'Kredit') {
                $saldo += $kas->qty;
            } else {
                $saldo -= $kas->qty;
            }
            $kas->saldo = $saldo;
            $kas->save();
        }
    }

    public function index(Request $request)
    {
        $query = Transaksi::with('customer');

        // Filter berdasarkan kolom yang dipilih dan Search Server Side
        if ($request->filled('search_by') && $request->filled('search')) {
            $searchBy = $request->search_by;
            $search = $request->search;

            if ($searchBy == 'customer') {
                $query->whereHas('customer', function($q) use ($search) {
                    $q->where('nama', 'like', "%$search%");
                });
            } elseif ($searchBy == 'alamat') {
                $query->whereHas('customer', function($q) use ($search) {
                    $q->where('alamat', 'like', "%$search%");
                });
            } elseif ($searchBy == 'sales') {
                $query->where('sales', 'like', "%$search%");
            } else {
                $query->where($searchBy, 'like', "%$search%");
            }
        }

        // Filter tanggal
        if ($request->filled('start_date')) {
            $query->whereDate('tanggal', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('tanggal', '<=', $request->end_date);
        }

        $transactions = $query->orderBy('tanggal', 'desc')->paginate(10)->withQueryString();

        return view('transaksi.lihat_nota', compact('transactions'));
    }

    public function penjualan(Request $request){
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
        $inventory = app(\App\Http\Controllers\PanelController::class)->getKodeSummary();

        return view('transaksi.penjualan', compact('noTransaksi', 'inventory'));
    }

    public function getByGroupId($group_id)
    {
        $panel = Panel::where('group_id', $group_id)->first();

        if (!$panel) {
            return response()->json(['error' => 'Panel not found'], 404);
        }

        return response()->json($panel);
    }

    /**
     * Store a sales transaction.
     */
    public function store(Request $request){
        $request->validate([
            'no_transaksi' => 'required|string|unique:transaksi,no_transaksi',
            'tanggal' => 'required|date',
            'kode_customer' => 'required|exists:customers,kode_customer',
            'sales' => 'required|exists:stok_owners,kode_stok_owner', // Validasi sales
            'subtotal' => 'required|numeric',
            'grand_total' => 'required|numeric',
            'items' => 'required|array',
            'items.*.kodeBarang' => 'required|exists:panels,group_id', // Validasi kode_barang
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
                'is_edited' => false,
            ]);

            // Get customer for stock mutation
            $customer = Customer::where('kode_customer', $request->kode_customer)->first();
            $customerName = $customer ? $customer->nama : 'Unknown Customer';

            // Format transaction number for stock mutation
            $creator = Auth::check() ? Auth::user()->name : 'ADMIN';
            $noTransaksi = $request->no_transaksi . " ({$creator})";

            // Create transaction items
            foreach ($request->items as $item) {
                TransaksiItem::create([
                    'transaksi_id' => $transaksi->id,
                    'no_transaksi' => $request->no_transaksi,
                    'kode_barang' => $item['kodeBarang'],
                    'nama_barang' => $item['namaBarang'],
                    'keterangan' => $item['keterangan'] ?? null,
                    'harga' => $item['harga'],
                    'panjang' => $item['panjang'] ?? 0,
                    'lebar' => $item['lebar'] ?? 0,
                    'qty' => $item['qty'],
                    'diskon' => $item['diskon'] ?? 0,
                    'total' => $item['total'],
                ]);

                // Record the sale in stock mutation
                $this->stockController->recordSale(
                    $item['kodeBarang'],
                    $item['namaBarang'],
                    $noTransaksi,
                    $request->tanggal,
                    $request->no_transaksi,
                    $customerName . ' (' . $request->kode_customer . ')',
                    $item['qty'],
                    $request->sales,
                    'LBR'
                );
            }

            DB::commit();

            foreach ($request->items as $item){
                $panels = Panel::where('group_id', $item['kodeBarang'])
                ->where('available', true)
                ->limit($item['qty'])
                ->get();

                foreach ($panels as $panel){
                    $panel->available = false;
                    $panel->save();
                }
            }

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
     * Show the form for editing a transaction
     */
    public function edit($id)
    {
        $transaction = Transaksi::with(['items', 'customer'])->findOrFail($id);
        
        // Check if transaction can be edited (only if not canceled)
        if (strpos($transaction->status, 'canceled') !== false) {
            return redirect()->route('transaksi.index')->with('error', 'Transaksi yang sudah dibatalkan tidak dapat diedit.');
        }
        
        // Get customer info
        $customer = null;
        if ($transaction->customer) {
            $customer = $transaction->kode_customer . ' - ' . $transaction->customer->nama;
        }
        
        $inventory = app(\App\Http\Controllers\PanelController::class)->getKodeSummary();
        
        return view('transaksi.edit', compact('transaction', 'customer', 'inventory'));
    }

    /**
     * Update a transaction
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'tanggal' => 'required|date',
            'kode_customer' => 'required|exists:customers,kode_customer',
            'sales' => 'required|exists:stok_owners,kode_stok_owner',
            'subtotal' => 'required|numeric',
            'grand_total' => 'required|numeric',
            'items' => 'required|array',
            'items.*.kodeBarang' => 'required|string',
            'items.*.harga' => 'required|numeric',
            'items.*.qty' => 'required|numeric',
            'edit_reason' => 'required|string|max:255',
        ]);
        
        try {
            DB::beginTransaction();
            
            // Find transaction
            $transaksi = Transaksi::findOrFail($id);
            $noTransaksi = $transaksi->no_transaksi; // Keep the original nota
            
            // Store original values for kas calculation
            $originalGrandTotal = $transaksi->grand_total;
            $originalCaraBayar = $transaksi->cara_bayar;
            
            // Check if transaction can be edited
            if (strpos($transaksi->status, 'canceled') !== false) {
                return redirect()->back()->with('error', 'Transaksi yang sudah dibatalkan tidak dapat diedit.');
            }
            
            // Current datetime
            $currentDateTime = now();
            
            // Get customer name for stock mutation record
            $customer = Customer::where('kode_customer', $request->kode_customer)->first();
            $customerName = $customer ? $customer->nama : 'Unknown Customer';
            
            // Get creator name from authenticated user or default to 'ADMIN'
            $editor = Auth::check() ? Auth::user()->name : 'ADMIN';
            
            // Format transaction number for mutation record
            $noTransaksiMutasi = $noTransaksi . " ({$editor}) [UPDATED]";
            
            // Get original items to handle stock reversal
            $originalItems = TransaksiItem::where('transaksi_id', $id)->get();
            
            // Track panels to restore to available status
            $panelsToRestore = [];
            
            // Revert stock changes for each original item
            foreach ($originalItems as $item) {
                // Find panels with this group_id that were marked unavailable from this transaction
                $panels = Panel::where('group_id', $item->kode_barang)
                    ->where('available', false)
                    ->orderBy('created_at', 'desc')
                    ->limit($item->qty)
                    ->get();
                
                foreach ($panels as $panel) {
                    $panelsToRestore[] = $panel->id;
                }
                
                // Record purchase to reverse the original sale in stock mutation
                $this->stockController->recordPurchase(
                    $item->kode_barang,
                    $item->nama_barang,
                    $noTransaksiMutasi,
                    now(),
                    $noTransaksi . ' (reversal)',
                    $customerName . ' (' . $request->kode_customer . ')',
                    $item->qty,
                    'LBR',
                    'Sale reversal for update',
                    $editor,
                    $transaksi->sales
                );
            }
            
            // Mark the panels as available
            if (!empty($panelsToRestore)) {
                Panel::whereIn('id', $panelsToRestore)->update(['available' => true]);
            }
            
            // Update transaction with proper timestamps for customer data tracking
            $transaksi->update([
                'tanggal' => $request->tanggal,
                'kode_customer' => $request->kode_customer,
                'sales' => $request->sales,
                'pembayaran' => $request->pembayaran,
                'cara_bayar' => $request->cara_bayar,
                'tanggal_jadi' => $request->tanggal_jadi,
                'subtotal' => $request->subtotal,
                'discount' => $request->discount ?? 0,
                'disc_rupiah' => $request->disc_rupiah ?? 0,
                'ppn' => $request->ppn ?? 0,
                'dp' => $request->dp ?? 0,
                'grand_total' => $request->grand_total,
                'updated_at' => $currentDateTime, // This helps track when changes occurred
                'is_edited' => true,
                'edited_by' => $editor,
                'edited_at' => $currentDateTime,
                'edit_reason' => $request->edit_reason,
            ]);
            
            // Delete all existing items
            TransaksiItem::where('transaksi_id', $id)->delete();
            
            // Create new transaction items and update stock
            foreach ($request->items as $item) {
                // Create transaction item
                TransaksiItem::create([
                    'transaksi_id' => $transaksi->id,
                    'no_transaksi' => $noTransaksi,
                    'kode_barang' => $item['kodeBarang'],
                    'nama_barang' => $item['namaBarang'],
                    'keterangan' => $item['keterangan'] ?? null,
                    'harga' => $item['harga'],
                    'panjang' => $item['panjang'] ?? 0,
                    'lebar' => $item['lebar'] ?? 0,
                    'qty' => $item['qty'],
                    'diskon' => $item['diskon'] ?? 0,
                    'total' => $item['total'],
                ]);
                
                // Record new sale in stock mutation
                $this->stockController->recordSale(
                    $item['kodeBarang'],
                    $item['namaBarang'],
                    $noTransaksiMutasi,
                    $currentDateTime,
                    $noTransaksi . ' (updated)',
                    $customerName . ' (' . $request->kode_customer . ')',
                    $item['qty'],
                    $transaksi->sales,
                    'LBR'
                );
                
                // Mark panels as unavailable
                $availablePanels = Panel::where('group_id', $item['kodeBarang'])
                    ->where('available', true)
                    ->limit($item['qty'])
                    ->get();
                
                foreach ($availablePanels as $panel) {
                    $panel->available = false;
                    $panel->save();
                }
            }
            
            // Handle Kas adjustment for cash transactions
            $newGrandTotal = floatval($request->grand_total);
            $wasOriginalCash = $this->isCashPayment($originalCaraBayar);
            $isNewCash = $this->isCashPayment($request->cara_bayar);
            
            if ($wasOriginalCash || $isNewCash) {
                // Case 1: Was cash, now not cash - Debit the full original amount
                if ($wasOriginalCash && !$isNewCash) {
                    Kas::create([
                        'name' => "Edit Transaksi: {$noTransaksi} (Non-Tunai)",
                        'description' => "Transaksi diubah dari tunai ke {$request->cara_bayar} oleh {$editor}. Alasan: {$request->edit_reason}",
                        'qty' => $originalGrandTotal,
                        'type' => 'Debit',
                        'saldo' => 0, // Will be calculated by adjustKasSaldo
                        'is_manual' => false,
                    ]);
                }
                // Case 2: Was not cash, now cash - Credit the full new amount
                elseif (!$wasOriginalCash && $isNewCash) {
                    Kas::create([
                        'name' => "Edit Transaksi: {$noTransaksi} (Tunai)",
                        'description' => "Transaksi diubah dari {$originalCaraBayar} ke tunai oleh {$editor}. Alasan: {$request->edit_reason}",
                        'qty' => $newGrandTotal,
                        'type' => 'Kredit',
                        'saldo' => 0, // Will be calculated by adjustKasSaldo
                        'is_manual' => false,
                    ]);
                }
                // Case 3: Both cash - Credit/Debit the difference
                elseif ($wasOriginalCash && $isNewCash) {
                    $difference = $newGrandTotal - $originalGrandTotal;
                    if ($difference != 0) {
                        Kas::create([
                            'name' => "Edit Transaksi: {$noTransaksi}",
                            'description' => "Transaksi diubah oleh {$editor}. Total: " . number_format($originalGrandTotal, 0, ',', '.') . " → " . number_format($newGrandTotal, 0, ',', '.') . ". Alasan: {$request->edit_reason}",
                            'qty' => abs($difference),
                            'type' => $difference > 0 ? 'Kredit' : 'Debit',
                            'saldo' => 0, // Will be calculated by adjustKasSaldo
                            'is_manual' => false,
                        ]);
                    }
                }
                
                // Recalculate all kas saldo
                $this->adjustKasSaldo();
            }
            
            DB::commit();
            
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Transaksi berhasil diperbarui.',
                    'redirect' => route('transaksi.shownota', $transaksi->id)
                ]);
            }
            
            // Check if request came from customer data page and redirect accordingly
            if ($request->has('from_customer_data')) {
                return redirect()->route('transaksi.penjualancustomer')
                    ->with('success', 'Transaksi berhasil diperbarui. Data customer telah diperbarui.');
            }
            
         // For non-AJAX requests
            return redirect()->route('transaksi.shownota', $transaksi->id)
                ->with('success', 'Transaksi berhasil diperbarui.');
                    
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Error in transaksi update:', ['exception' => $e->getMessage()]);
                
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Terjadi kesalahan: ' . $e->getMessage()
                    ], 500);
                }
                
                return redirect()->back()
                    ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
            }
    }

    /**
     * Cancel Transaction Function
     */
    public function cancelTransaction(Request $request, $id)
    {
        // Validate cancel reason
        $request->validate([
            'cancel_reason' => 'required|string|max:255',
        ]);
        
        DB::beginTransaction();
        try {
            $transaksi = Transaksi::with(['items', 'customer'])->findOrFail($id);

            // Cegah double cancel
            if (strpos($transaksi->status, 'canceled') !== false) {
                return back()->with('error', 'Transaksi sudah dibatalkan.');
            }

            // Get current user name
            $userName = Auth::check() ? Auth::user()->name : 'SYSTEM';
            
            // Kembalikan stock & catat mutasi pembatalan
            foreach ($transaksi->items as $item) {
                // 1. Update Panel model availability
                $panels = Panel::where('group_id', $item->kode_barang)
                    ->where('available', false)
                    ->limit($item->qty)
                    ->get();
                    
                foreach ($panels as $panel) {
                    $panel->available = true;
                    $panel->save();
                }

                // 2. Update Stock model
                $stock = \App\Models\Stock::where('kode_barang', $item->kode_barang)->first();

                if ($stock) {
                    $stock->good_stock += $item->qty;
                    $stock->save();
                } else {
                    // Create stock record if doesn't exist
                    $stock = \App\Models\Stock::create([
                        'kode_barang' => $item->kode_barang,
                        'nama_barang' => $item->nama_barang,
                        'good_stock' => $item->qty, 
                        'bad_stock' => 0,
                        'satuan' => 'LBR',
                        'so' => $transaksi->sales
                    ]);
                }

                // 3. Add detailed cancellation mutation record
                \App\Models\StockMutation::create([
                    'kode_barang' => $item->kode_barang,
                    'nama_barang' => $item->nama_barang,
                    'no_transaksi' => $transaksi->no_transaksi . " (DIBATALKAN)",
                    'tanggal' => now(),
                    'no_nota' => $transaksi->no_transaksi,
                    'supplier_customer' => $transaksi->customer->nama ?? '-',
                    'plus' => $item->qty,
                    'minus' => 0,
                    'total' => $stock->good_stock,
                    'so' => $transaksi->sales,
                    'satuan' => 'LBR',
                    'keterangan' => "Pembatalan transaksi oleh {$userName}: " . $request->cancel_reason,
                    'created_by' => $userName
                ]);
            }

            // Handle Kas adjustment for cash transactions
            if ($this->isCashPayment($transaksi->cara_bayar)) {
                Kas::create([
                    'name' => "Batal Transaksi: {$transaksi->no_transaksi}",
                    'description' => "Transaksi dibatalkan oleh {$userName}. Alasan: {$request->cancel_reason}",
                    'qty' => $transaksi->grand_total,
                    'type' => 'Debit',
                    'saldo' => 0, // Will be calculated by adjustKasSaldo
                    'is_manual' => false,
                ]);
                
                // Recalculate all kas saldo
                $this->adjustKasSaldo();
            }

            // Store cancellation info with proper timestamp
            $transaksi->status = "canceled";
            $transaksi->canceled_by = $userName;
            $transaksi->canceled_at = now(); // This is crucial for customer data updates
            $transaksi->cancel_reason = $request->cancel_reason;
            $transaksi->save();

            DB::commit();

            // Check if request came from customer data page and redirect accordingly
            if ($request->has('from_customer_data')) {
                return redirect()->route('transaksi.penjualancustomer')
                    ->with('success', 'Transaksi berhasil dibatalkan. Data customer telah diperbarui.');
            }

            return back()->with('success', 'Transaksi berhasil dibatalkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in cancelTransaction:', ['message' => $e->getMessage()]);
            return back()->with('error', 'Gagal membatalkan transaksi: ' . $e->getMessage());
        }
    }

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
     * Get Transaksi list by customer(Request $request)
     */
    public function getTransaksiByCustomer(Request $request)
    {
        $keyword = $request->get('keyword');
        $kode_customer = $request->get('kode_customer');
        $transaksi = DB::table('transaksi')
            ->where('kode_customer', 'like', "%{$kode_customer}%")
            ->where('no_transaksi', 'like', "%{$keyword}%")
            ->limit(10)
            ->get();
            
        return response()->json($transaksi);
    }
    /**
     * Get rincian data transaksi_items (list) by transaksi_id 
     */
    public function getRincianTransaksi($id){
        $rincianTransaksi = TransaksiItem::where('transaksi_id', $id)->get();

        return response()->json($rincianTransaksi);
    }

    /**
     * Get transaksi data by kode_customer for surat jalan autocomplete
     */
    public function getTransaksi(Request $request){
        $query = $request->get('query');
        $kodeCustomer = $request->get('kode_customer');
    
        try {
            $transaksi = Transaksi::when($kodeCustomer, function ($queryBuilder) use ($kodeCustomer) {
                    $queryBuilder->where('kode_customer', $kodeCustomer);
                })
                ->when($query, function ($queryBuilder) use ($query) {
                    $queryBuilder->where('no_transaksi', 'like', "%{$query}%");
                })
                ->get(['id', 'no_transaksi', 'tanggal']); // Hanya ambil kolom yang diperlukan
                
                return response()->json($transaksi);
            } catch (\Exception $e) {
                Log::error('Error in getTransaksi:', ['message' => $e->getMessage()]);
                return response()->json(['error' => 'Internal Server Error'], 500);
            }
        }
        
    /**
     * Get transaction data from customers
     */
     public function penjualanPercustomer(Request $request){
    // Get search filters
        $search = $request->get('search');
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');
        $status = $request->get('status', 'all'); // all, active, canceled

        // Fetch all customers with proper column selection
        // Check what columns actually exist in your customers table first
        try {
            $customers = Customer::select([
                'kode_customer', 
                'nama', 
                'alamat', 
                'telepon'
                // Only include 'hp' if it exists in your database
                // 'hp' 
            ])->get();
        } catch (\Exception $e) {
            // Fallback: get all customers and let Eloquent handle it
            $customers = Customer::all();
        }

        // Apply customer name search filter if provided
        if ($search) {
            $customers = $customers->filter(function($customer) use ($search) {
                return stripos($customer->nama, $search) !== false || 
                    stripos($customer->kode_customer, $search) !== false;
            });
        }

        // Fetch all transactions with related data
        $transactionsQuery = Transaksi::with(['items', 'customer'])
            ->select([
                'id',
                'no_transaksi',
                'tanggal',
                'kode_customer',
                'grand_total',
                'status',
                'is_edited',
                'canceled_at',
                'edited_at',
                'edited_by',
                'canceled_by',
                'edit_reason',
                'cancel_reason',
                'created_at',
                'updated_at'
            ]);

        // Apply date filters if provided
        if ($dateFrom) {
            $transactionsQuery->whereDate('tanggal', '>=', $dateFrom);
        }
        if ($dateTo) {
            $transactionsQuery->whereDate('tanggal', '<=', $dateTo);
        }

        // Apply status filter
        if ($status === 'active') {
            $transactionsQuery->where('status', '!=', 'canceled')
                            ->whereNull('canceled_at');
        } elseif ($status === 'canceled') {
            $transactionsQuery->where('status', '=', 'canceled')
                            ->whereNotNull('canceled_at');
        } elseif ($status === 'edited') {
            $transactionsQuery->where('is_edited', true);
        }

        $transactions = $transactionsQuery->orderBy('tanggal', 'desc')->get();

        // Get selected customer transactions if requested
        $selectedCustomerTransactions = [];
        $selectedCustomer = null;
        if ($request->has('kode_customer') && $request->kode_customer) {
            $selectedCustomer = Customer::where('kode_customer', $request->kode_customer)->first();
            $selectedCustomerTransactions = $transactions->where('kode_customer', $request->kode_customer);
        }

        return view('transaksi.datapenjualanpercustomer', compact(
            'customers', 
            'transactions',
            'selectedCustomerTransactions', 
            'selectedCustomer',
            'search',
            'dateFrom',
            'dateTo',
            'status'
        ));        
    }

    public function getPenjualancustomer(Request $request){
        try {
            $kodeCustomer = $request->get('kode_customer');
            $dateFrom = $request->get('date_from');
            $dateTo = $request->get('date_to');
            $status = $request->get('status', 'all');

            if (!$kodeCustomer) {
                return response()->json(['error' => 'Customer code is required'], 400);
            }

            // Get customer info
            $customer = Customer::where('kode_customer', $kodeCustomer)->first();
            if (!$customer) {
                return response()->json(['error' => 'Customer not found'], 404);
            }

            // Build transactions query
            $transactionsQuery = Transaksi::with(['items'])
                ->where('kode_customer', $kodeCustomer);

            // Apply status filter
            if ($status === 'active') {
                $transactionsQuery->where('status', '!=', 'canceled')
                                 ->whereNull('canceled_at');
            } elseif ($status === 'canceled') {
                $transactionsQuery->where('status', '=', 'canceled')
                                 ->whereNotNull('canceled_at');
            }

            // Apply date filters
            if ($dateFrom) {
                $transactionsQuery->whereDate('tanggal', '>=', $dateFrom);
            }
            if ($dateTo) {
                $transactionsQuery->whereDate('tanggal', '<=', $dateTo);
            }

            $transactions = $transactionsQuery->orderBy('tanggal', 'desc')->get();

            // Calculate summary statistics
            $summary = [
                'total_transaksi_aktif' => $transactions->where('status', '!=', 'canceled')->whereNull('canceled_at')->count(),
                'total_transaksi_batal' => $transactions->where('status', '=', 'canceled')->whereNotNull('canceled_at')->count(),
                'total_nilai_aktif' => $transactions->where('status', '!=', 'canceled')->whereNull('canceled_at')->sum('grand_total'),
                'total_nilai_batal' => $transactions->where('status', '=', 'canceled')->whereNotNull('canceled_at')->sum('grand_total'),
                'transaksi_terakhir' => $transactions->where('status', '!=', 'canceled')->whereNull('canceled_at')->max('tanggal')
            ];

            return response()->json([
                'customer' => $customer,
                'transactions' => $transactions,
                'summary' => $summary
            ]);

        } catch (\Exception $e) {
            Log::error('Error in getPenjualancustomer:', ['message' => $e->getMessage()]);
            return response()->json(['error' => 'Internal Server Error'], 500);
        }
    }

    // Mencari transaksi berdasarkan id untuk surat jalan
    public function getTransaksiItems($transaksiId)
    {
        try {
            $transaksiItems = TransaksiItem::where('transaksi_id', $transaksiId)
                ->get()
                ->map(function ($item) {
    
                    return [
                        'id' => $item->id,
                        'kode_barang' => $item->kode_barang,
                        'nama_barang' => $item->nama_barang,
                        'keterangan' => $item->keterangan,
                        'panjang' => $item->panjang,
                        'lebar' => $item->lebar,
                        'qty' => $item->qty,
                    ];
                });
    
            return response()->json($transaksiItems);
        } catch (\Exception $e) {
            Log::error('Error in getTransaksiItems:', ['message' => $e->getMessage()]);
            return response()->json(['error' => 'Internal Server Error'], 500);
        }
    }

    /**
     * Show the invoice (nota) for a transaction
     */
    public function showNota($id)
    {
        $transaction = Transaksi::with('items', 'customer')->findOrFail($id);

        // Split items into chunks of 10 per page
        $itemsPerPage = 10;
        $groupedItems = $transaction->items->chunk($itemsPerPage);

        return view('transaksi.nota', [
            'transaction' => $transaction,
            'groupedItems' => $groupedItems
        ]);
    }

    public function nota($id)
    {
        $transaction = Transaksi::with('items', 'customer')->findOrFail($id);

        // Split items into chunks of 10 per page
        $itemsPerPage = 10;
        $groupedItems = $transaction->items->chunk($itemsPerPage);

        $pdf = Pdf::loadView('transaksi.nota', [
            'transaction' => $transaction,
            'groupedItems' => $groupedItems
        ]);

        return $pdf->stream('nota.pdf');
    }

    public function listNota()
    {
        // Fetch all transactions
        $transactions = Transaksi::with('items')->orderBy('created_at', 'desc')->paginate(10);

        return view('transaksi.lihat_nota', compact('transactions'));
    }

}