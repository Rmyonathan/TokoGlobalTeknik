<?php

namespace App\Http\Controllers;

use App\Models\KodeBarang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Pembelian;
use App\Models\PembelianItem;
use App\Models\Supplier;
use Illuminate\Support\Facades\Log;
use App\Models\Panel;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\StockBatch;
use App\Services\AccountingService;


class PembelianController extends Controller
{
    protected $stockController;
    protected $panelController;

    public function __construct(StockController $stockController, PanelController $panelController)
    {
        $this->stockController = $stockController;
        $this->panelController = $panelController;
    }

    /**
     * Display the purchase transaction form.
     */
    public function index()
    {
        // Generate nomor nota baru yang unik
        $currentMonth = date('m');
        $currentYear = date('y');
        $notaPrefix = "BL/{$currentMonth}/{$currentYear}-";
        
        // Cari nomor terakhir untuk bulan dan tahun ini
        $lastPurchase = Pembelian::where('nota', 'like', $notaPrefix . '%')
            ->orderBy('nota', 'desc')
            ->first();

        // Generate nomor nota baru
        if ($lastPurchase) {
            // Ambil angka terakhir dari nota
            $lastNumber = (int) substr($lastPurchase->nota, strrpos($lastPurchase->nota, '-') + 1);
            $newNumber = $lastNumber + 1;
        } else {
            // Jika belum ada pembelian untuk bulan ini, mulai dari 1
            $newNumber = 1;
        }

        // Pastikan nomor nota unik
        do {
            $nota = $notaPrefix . str_pad($newNumber, 5, '0', STR_PAD_LEFT);
            $exists = Pembelian::where('nota', $nota)->exists();
            if ($exists) {
                $newNumber++;
            }
        } while ($exists);

        // Get KodeBarang data for dropdown
        $kodeBarangs = KodeBarang::orderBy('name')->get();

        return view('pembelian.addpembelian', compact('nota', 'kodeBarangs'));
    }

    /**
     * Store a purchase transaction.
     */
    public function store(Request $request)
    {
        // In the store method, update the validation
        $request->validate([
            'nota' => 'required|string|unique:pembelian,nota',
            'no_po' => 'required|string|max:50',
            'no_surat_jalan' => 'nullable|string',
            'tanggal' => 'required|date',
            'kode_supplier' => 'required|exists:suppliers,kode_supplier',
            'subtotal' => 'required|numeric',
            'grand_total' => 'required|numeric',
            'items' => 'required|array',
            'items.*.kodeBarang' => 'required|string',
            'items.*.harga' => 'required|numeric',
            'items.*.qty' => 'required|numeric|min:0.01',
            'hari_tempo' => 'nullable|integer|min:0',
            'tanggal_jatuh_tempo' => 'nullable|date|after_or_equal:tanggal',
        ]);
        
        try {
            DB::beginTransaction();
            
            // Pastikan nota unik sebelum menyimpan
            $nota = $request->nota;
            $originalNota = $nota;
            $counter = 1;
            
            while (Pembelian::where('nota', $nota)->exists()) {
                $nota = $originalNota . '-' . $counter;
                $counter++;
            }
            
            // Use current time for the transaction
            $currentDateTime = now();
            $tanggalWithTime = $currentDateTime->format('Y-m-d H:i:s');
            
            // Get supplier name for stock mutation record
            $supplier = Supplier::where('kode_supplier', $request->kode_supplier)->first();
            $supplierName = $supplier ? $supplier->nama : 'Unknown Supplier';
            
            // Create purchase
            $pembelian = Pembelian::create([
                'nota' => $nota,
                'no_po' => $request->no_po,
                'no_surat_jalan' => $request->no_surat_jalan,
                'tanggal' => $tanggalWithTime, // Store with time
                'kode_supplier' => $request->kode_supplier,
                'pembayaran' => $request->pembayaran ?? 'Tunai',
                'cara_bayar' => $request->cara_bayar,
                'hari_tempo' => $request->hari_tempo ?? 0,
                'tanggal_jatuh_tempo' => $request->tanggal_jatuh_tempo ?? null,
                'subtotal' => $request->subtotal,
                'diskon' => $request->diskon ?? 0,
                'ppn' => $request->ppn ?? 0,
                'grand_total' => $request->grand_total,
                'created_at' => $currentDateTime,
            ]);
            
            // Get creator name from request or default to 'ADMIN'
            $creator = Auth::check() ? Auth::user()->name : 'ADMIN';
            
            // Format transaction number for mutation record - cleaner without time
            $noTransaksi = "BL-" . date('m/y', strtotime($request->tanggal)) . "-" . 
                            substr($request->nota, strrpos($request->nota, '-') + 1) . 
                            " ({$creator})";
            
            // Create purchase items, update stock mutation, and add inventory
            foreach ($request->items as $item) {
                // Create purchase item
                $pembelianItem = PembelianItem::create([
                    'nota' => $request->nota,
                    'kode_barang' => $item['kodeBarang'],
                    'nama_barang' => $item['namaBarang'],
                    'keterangan' => $item['keterangan'] ?? null,
                    'harga' => $item['harga'],
                    'qty' => $item['qty'],
                    'diskon' => $item['diskon'] ?? 0,
                    'total' => $item['total'],
                    'created_at' => $currentDateTime,
                ]);

                // Create StockBatch untuk sistem FIFO
                $kodeBarang = KodeBarang::where('kode_barang', $item['kodeBarang'])->first();
                if ($kodeBarang) {
                     $stockBatch =StockBatch::create([
                        'kode_barang_id' => $kodeBarang->id,
                        'pembelian_item_id' => $pembelianItem->id,
                        'qty_masuk' => $item['qty'],
                        'qty_sisa' => $item['qty'], // Awalnya sama dengan qty_masuk
                        'harga_beli' => $item['harga'],
                        'tanggal_masuk' => $request->tanggal,
                        'batch_number' => $request->nota . '-' . $item['kodeBarang'],
                        'keterangan' => 'Pembelian dari ' . $supplierName
                    ]);

                     Log::info('FIFO - Batch Baru Masuk', [
                        'nota' => $request->nota,
                        'kode_barang' => $item['kodeBarang'],
                        'nama_barang' => $item['namaBarang'],
                        'batch_id' => $stockBatch->id,
                        'qty_masuk' => $stockBatch->qty_masuk,
                        'qty_sisa' => $stockBatch->qty_sisa,
                        'harga_beli' => $stockBatch->harga_beli,
                        'tanggal_masuk' => $stockBatch->tanggal_masuk,
                        'created_by' => $creator,
                    ]);
                }
                
                // Record purchase in stock mutation (just for reporting)
                $this->stockController->recordPurchase(
                    $item['kodeBarang'],
                    $item['namaBarang'],
                    $noTransaksi,
                    $tanggalWithTime, // Use date with time
                    $request->nota,
                    $supplierName . ' (' . $request->kode_supplier . ')',
                    $item['qty'],
                    'LBR', // Unit of measure
                    'Purchase transaction', // Keterangan
                    $creator, // Created by
                    'default' // Stock owner
                );
                
                // Get the kode barang record
                $kodeBarang = KodeBarang::where('kode_barang', $item['kodeBarang'])->first();
                
                if ($kodeBarang) {
                    // Get a panel instance with this kode_barang to use as a template
                    $templatePanel = Panel::where('group_id', $item['kodeBarang'])->first();
                    
                    // Default values if no template exists
                    $panelName = $item['namaBarang'];
                    // $length = $kodeBarang->length ?? 0;
                    $cost = $item['harga']; // Use purchase price as cost for this purchase only
                    $price = $templatePanel ? $templatePanel->price : ($item['harga'] * 1.2); // 20% markup if no template
                    
                    // If template exists, use its values
                    if ($templatePanel) {
                        $panelName = $templatePanel->name;
                        // $length = $templatePanel->length ?? $length;
                        $price = $templatePanel->price;
                    }
                    
                    // Use the PanelController to add panels to inventory
                    $panelController = app()->make(PanelController::class);
                    
                    // Check if the method accepts the timestamp parameter
                    try {
                        $result = $panelController->addPanelsToInventory(
                            $panelName, 
                            $cost, 
                            $item['kodeBarang'], 
                            (int) $item['qty']
                        );
                    } catch (\ArgumentCountError $e) {
                        // If the method doesn't accept the timestamp, use the original method
                        $result = $panelController->addPanelsToInventory(
                            $panelName, 
                            $cost, 
                            $item['kodeBarang'], 
                            (int) $item['qty']
                        );
                    }
                    
                    // Log the result
                    Log::info('Added panels to inventory:', ['result' => $result]);
                    
                    // Update cost di master barang dengan harga pembelian terbaru
                    $kodeBarang->cost = $item['harga'];
                    $kodeBarang->save();
                    Log::info('Updated master barang cost:', [
                        'kode_barang' => $item['kodeBarang'],
                        'new_cost' => $item['harga']
                    ]);
                } else {
                    Log::warning('KodeBarang not found for purchase item:', ['kode_barang' => $item['kodeBarang']]);
                }
            }
            
            DB::commit();

            // Create accounting journal for purchase (DR Persediaan, DR Piutang PPN, CR Kas/Utang Usaha)
            try {
                app(AccountingService::class)->createJournalFromPurchase($pembelian);
            } catch (\Exception $e) {
                Log::warning('Accounting journal for purchase failed', ['message' => $e->getMessage(), 'nota' => $pembelian->nota]);
            }

            return response()->json([
                'id' => $pembelian->id,
                'nota' => $pembelian->nota,
                'tanggal' => $pembelian->tanggal,
                'supplier' => $pembelian->supplierRelation->nama ?? 'N/A',
                'grand_total' => $pembelian->grand_total,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in pembelian store:', ['exception' => $e->getMessage()]);
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Search for suppliers
     */
    public function searchSuppliers(Request $request)
    {
        $keyword = $request->keyword;

        $suppliers = Supplier::where('kode_supplier', 'like', "%{$keyword}%")
            ->orWhere('nama', 'like', "%{$keyword}%")
            ->limit(10)
            ->get();

        return response()->json($suppliers);
    }

    /**
     * Get purchase data
     */
    public function getPurchase($id)
    {
        $purchase = Pembelian::with('items')->findOrFail($id);

        return response()->json($purchase);
    }

    /**
     * Show the invoice (nota) for a purchase
     */
    public function showNota($id)
    {
        $purchase = Pembelian::with('items', 'supplierRelation')->findOrFail($id);

        return view('pembelian.nota_pembelian', compact('purchase'));
    }

    public function nota($nota)
    {
        $purchase = Pembelian::with('items', 'supplierRelation')->where('nota', $nota)->firstOrFail();
        return view('pembelian.nota_pembelian', compact('purchase'));
    }

    public function listNota(Request $request)
    {
        // Get search parameters
        $searchBy = $request->input('search_by', '');
        $search = $request->input('search', '');
        $startDate = $request->input('startDate', '');
        $endDate = $request->input('endDate', '');
        
        // Build the query for Pembelian
        $query = Pembelian::with('items', 'supplierRelation');
        
        // Apply search filter if search term exists and search_by is specified
        if (!empty($search) && !empty($searchBy)) {
            if ($searchBy === 'nota') {
                $query->where('nota', 'like', "%{$search}%");
            } else if ($searchBy === 'kode_supplier') {
                $query->where('kode_supplier', 'like', "%{$search}%");
            } else if ($searchBy === 'nama_supplier') {
                $query->whereHas('supplierRelation', function($q) use ($search) {
                    $q->where('nama', 'like', "%{$search}%");
                });
            } else if ($searchBy === 'cara_bayar') {
                $query->where('cara_bayar', 'like', "%{$search}%");
            }
        } else if (!empty($search)) {
            // If search_by is not specified but search term exists, search in all relevant fields
            $query->where(function($q) use ($search) {
                $q->where('nota', 'like', "%{$search}%")
                ->orWhere('kode_supplier', 'like', "%{$search}%")
                ->orWhere('cara_bayar', 'like', "%{$search}%")
                ->orWhereHas('supplierRelation', function($sq) use ($search) {
                    $sq->where('nama', 'like', "%{$search}%");
                });
            });
        }
        
        // Apply date filter if provided
        if (!empty($startDate) && !empty($endDate)) {
            $query->whereDate('tanggal', '>=', $startDate)
                ->whereDate('tanggal', '<=', $endDate);
        } else if (!empty($startDate)) {
            $query->whereDate('tanggal', '>=', $startDate);
        } else if (!empty($endDate)) {
            $query->whereDate('tanggal', '<=', $endDate);
        }
        
        // Order by created_at descending
        $query->orderBy('created_at', 'desc');
        
        // Paginate the results and append query params to the pagination links
        $purchases = $query->paginate(10);
        $purchases->appends([
            'search_by' => $searchBy,
            'search' => $search,
            'startDate' => $startDate,
            'endDate' => $endDate
        ]);
        
        return view('pembelian.lihat_nota_pembelian', compact(
            'purchases', 
            'searchBy',
            'search', 
            'startDate', 
            'endDate'
        ));
    }

    /**
     * Show the form for editing the specified purchase.
     */
    public function edit($id)
    {
        $purchase = Pembelian::with(['items', 'supplierRelation'])->findOrFail($id);
        
        // Get the supplier info
        $supplier = null;
        if ($purchase->supplierRelation) {
            $supplier = $purchase->kode_supplier . ' - ' . $purchase->supplierRelation->nama;
        }
        
        return view('pembelian.editpembelian', compact('purchase', 'supplier'));
    }
    
    /**
     * Update the specified purchase in storage.
     */
    public function update(Request $request, $id)
    {
        // In the store method, update the validation
        $request->validate([
            'nota' => 'required|string|unique:pembelian,nota,'.$id,
            'no_surat_jalan' => 'nullable|string',
            'tanggal' => 'required|date',
            'kode_supplier' => 'required|exists:suppliers,kode_supplier',
            'subtotal' => 'required|numeric',
            'grand_total' => 'required|numeric',
            'items' => 'required|array',
            'items.*.kodeBarang' => 'required|string',
            'items.*.harga' => 'required|numeric',
            'items.*.qty' => 'required|numeric|min:0.01',
            'edit_reason' => 'required|string|max:255',
        ]);
        
        try {
            DB::beginTransaction();
            
            // Find purchase
            $pembelian = Pembelian::findOrFail($id);
            $nota = $pembelian->nota; // Keep the original nota
            
            // Use current time for the transaction
            $currentDateTime = now();
            $tanggalWithTime = $currentDateTime->format('Y-m-d H:i:s');
            
            // Get supplier name for stock mutation record
            $supplier = Supplier::where('kode_supplier', $request->kode_supplier)->first();
            $supplierName = $supplier ? $supplier->nama : 'Unknown Supplier';
            
            // Get creator name from authenticated user or default to 'ADMIN'
            $editor = Auth::check() ? Auth::user()->name : 'ADMIN';

            
            // Format transaction number for mutation record
            $noTransaksi = "BL-" . date('m/y', strtotime($request->tanggal)) . "-" . 
                        substr($nota, strrpos($nota, '-') + 1) . 
                        " ({$editor}) [UPDATED]";
            
            // Get the original items to remove from inventory
            $originalItems = PembelianItem::where('nota', $nota)->get();
            
            // Track panels to delete
            $panelsToDelete = [];
            
            // For each original item, find and mark panels for deletion
            foreach ($originalItems as $item) {
                // Find panels with this group_id that match the original purchase
                $panels = Panel::where('group_id', $item->kode_barang)
                    ->where('available', true)
                    ->orderBy('created_at', 'desc') // Get the most recently added first (likely from this purchase)
                    ->limit($item->qty)
                    ->get();
                
                foreach ($panels as $panel) {
                    $panelsToDelete[] = $panel->id;
                }
                
                // Record sale to reverse the original purchase in stock mutation
                $this->stockController->recordSale(
                    $item->kode_barang,
                    $item->nama_barang,
                    $noTransaksi,
                    now(), // Use current date/time for the reversal
                    $nota . ' (reversal)',
                    $supplierName . ' (' . $request->kode_supplier . ')',
                    $item->qty, // Same quantity as purchase, but as a "sale" to reduce stock
                    'LBR', // Unit of measure
                    'Purchase reversal for update', // Keterangan
                    $editor, // Created by
                    'default' // Stock owner
                );
            }
            
            // Delete the marked panels
            if (!empty($panelsToDelete)) {
                Panel::whereIn('id', $panelsToDelete)->delete();
            }
            
            // Update purchase
            $pembelian->update([
                'tanggal' => $tanggalWithTime, // Use date with time
                'kode_supplier' => $request->kode_supplier,
                'no_surat_jalan' => $request->no_surat_jalan,
                'pembayaran' => $request->metode_pembayaran ?? 'Tunai', // Updated field name
                'cara_bayar' => $request->cara_bayar,
                'subtotal' => $request->subtotal,
                'diskon' => $request->diskon ?? 0,
                'ppn' => $request->ppn ?? 0,
                'grand_total' => $request->grand_total,
                'updated_at' => $currentDateTime,
                'is_edited' => true,
                'edited_by' => $editor,
                'edited_at' => $currentDateTime,
                'edit_reason' => $request->edit_reason,
            ]);
            
            // Delete all existing items
            PembelianItem::where('nota', $nota)->delete();
            
            // Create new purchase items and add new inventory
            foreach ($request->items as $item) {
                PembelianItem::create([
                    'nota' => $nota,
                    'kode_barang' => $item['kodeBarang'],
                    'nama_barang' => $item['namaBarang'],
                    'keterangan' => $item['keterangan'] ?? null,
                    'harga' => $item['harga'],
                    'qty' => $item['qty'],
                    'diskon' => $item['diskon'] ?? 0,
                    'total' => $item['total'],
                    'created_at' => $currentDateTime,
                ]);
                
                // Record new purchase in stock mutation
                $this->stockController->recordPurchase(
                    $item['kodeBarang'],
                    $item['namaBarang'],
                    $noTransaksi,
                    $tanggalWithTime, // Use date with time
                    $nota . ' (updated)',
                    $supplierName . ' (' . $request->kode_supplier . ')',
                    $item['qty'],
                    'LBR', // Unit of measure
                    'Purchase transaction update', // Keterangan
                    $editor, // Created by
                    'default' // Stock owner
                );
                
                // Get the kode barang record
                $kodeBarang = KodeBarang::where('kode_barang', $item['kodeBarang'])->first();
                
                if ($kodeBarang) {
                    // Get a panel instance with this kode_barang to use as a template
                    $templatePanel = Panel::where('group_id', $item['kodeBarang'])->first();
                    
                    // Default values if no template exists
                    $panelName = $item['namaBarang'];
                    // $length = $kodeBarang->length ?? 0;
                    $cost = $item['harga']; // Use purchase price as cost
                    $price = $templatePanel ? $templatePanel->price : ($item['harga'] * 1.2); // 20% markup if no template
                    
                    // If template exists, use its values
                    if ($templatePanel) {
                        $panelName = $templatePanel->name;
                        // $length = $templatePanel->length ?? $length;
                        $price = $templatePanel->price;
                    }
                    
                    // Use the PanelController to add panels to inventory
                    $panelController = app()->make(PanelController::class);
                    
                    // Check if the method accepts the timestamp parameter
                    try {
                        $result = $panelController->addPanelsToInventory(
                            $panelName, 
                            $cost, 
                            $item['kodeBarang'], 
                            (int) $item['qty']
                        );
                    } catch (\ArgumentCountError $e) {
                        // If the method doesn't accept the timestamp, use the original method
                        $result = $panelController->addPanelsToInventory(
                            $panelName, 
                            $cost, 
                            $item['kodeBarang'], 
                            (int) $item['qty']
                        );
                    }
                    
                    // Log the result
                    Log::info('Added panels to inventory during update:', ['result' => $result]);
                    
                    // Update cost di master barang dengan harga pembelian terbaru
                    $kodeBarang->cost = $item['harga'];
                    $kodeBarang->save();
                    Log::info('Updated master barang cost during update:', [
                        'kode_barang' => $item['kodeBarang'],
                        'new_cost' => $item['harga']
                    ]);
                } else {
                    Log::warning('KodeBarang not found for updated purchase item:', ['kode_barang' => $item['kodeBarang']]);
                }
            }
            
            DB::commit();

            return response()->json([
                'success' => true,
                'id' => $pembelian->id,
                'nota' => $pembelian->nota,
                'message' => 'Pembelian berhasil diperbarui'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in pembelian update:', ['exception' => $e->getMessage()]);
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Remove the specified purchase from storage.
     */
    public function destroy($id)
    {
        try {
            DB::beginTransaction();
            
            // Find purchase
            $pembelian = Pembelian::findOrFail($id);
            $nota = $pembelian->nota;
            
            // Get supplier name for stock mutation record
            $supplier = $pembelian->supplierRelation;
            $supplierName = $supplier ? $supplier->nama : 'Unknown Supplier';
            
            // Get creator name or default to 'ADMIN'
            $creator = 'ADMIN';
            
            // Format transaction number for deletion record
            $noTransaksi = "BL-" . date('m/y', strtotime($pembelian->tanggal)) . "-" . 
                           substr($nota, strrpos($nota, '-') + 1) . 
                           " ({$creator}) [DELETED]";
            
            // Get the items to remove from inventory
            $items = PembelianItem::where('nota', $nota)->get();
            
            // Track panels to delete
            $panelsToDelete = [];
            
            foreach ($items as $item) {
                // Find panels with this group_id that match the purchase being deleted
                $panels = Panel::where('group_id', $item->kode_barang)
                    ->where('available', true)
                    ->orderBy('created_at', 'desc') // Get the most recently added first (likely from this purchase)
                    ->limit($item->qty)
                    ->get();
                
                foreach ($panels as $panel) {
                    $panelsToDelete[] = $panel->id;
                }
                
                // Record sale to reverse the purchase in stock mutation
                $this->stockController->recordSale(
                    $item->kode_barang,
                    $item->nama_barang,
                    $noTransaksi,
                    now(), // Use current date/time for the deletion
                    $nota . ' (deleted)',
                    $supplierName . ' (' . $pembelian->kode_supplier . ')',
                    $item->qty, // Same quantity as purchase, but as a "sale" to reduce stock
                    'default',                    
                    'LBR'
                );
            }
            
            // Delete the marked panels
            if (!empty($panelsToDelete)) {
                Log::info('Deleting panels:', ['panel_ids' => $panelsToDelete]);
                Panel::whereIn('id', $panelsToDelete)->delete();
            }
            
            // Delete all related items first
            PembelianItem::where('nota', $nota)->delete();
            
            // Delete the purchase
            $pembelian->delete();
            
            DB::commit();
            
            return redirect()->route('pembelian.nota.list')
                ->with('success', 'Nota pembelian berhasil dihapus');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in pembelian destroy:', ['exception' => $e->getMessage()]);
            
            return redirect()->route('pembelian.nota.list')
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
 * Cancel a purchase transaction
 */
    public function cancel(Request $request, $id)
    {
        $request->validate([
            'cancel_reason' => 'required|string|max:255',
        ]);

        try {
            DB::beginTransaction();
            
            // Find purchase
            $pembelian = Pembelian::findOrFail($id);
            
            // Check if already canceled
            if ($pembelian->status === 'canceled') {
                return redirect()->back()->with('error', 'Nota pembelian sudah dibatalkan sebelumnya.');
            }
            
            $nota = $pembelian->nota;
            
            // Get supplier name for stock mutation record
            $supplier = $pembelian->supplierRelation;
            $supplierName = $supplier ? $supplier->nama : 'Unknown Supplier';
            
            // Get current user or default to 'ADMIN'
            $canceledBy = Auth::check() ? Auth::user()->name : 'ADMIN';
            
            // Format transaction number for cancellation record - without time
            $noTransaksi = "BL-" . date('m/y', strtotime($pembelian->tanggal)) . "-" . 
                        substr($nota, strrpos($nota, '-') + 1) . 
                        " ({$canceledBy}) [CANCELED]";
            
            // Get the items to replenish inventory
            $items = PembelianItem::where('nota', $nota)->get();
            
            // Track panels to mark as unavailable
            $panelsToCancel = [];
            
            // Get current date and time for records
            $currentDateTime = now()->format('Y-m-d H:i:s');
            
            foreach ($items as $item) {
                // Find panels with this group_id that match the purchase being canceled
                $panels = Panel::where('group_id', $item->kode_barang)
                    ->where('available', true)
                    ->orderBy('created_at', 'desc') // Get the most recently added first (likely from this purchase)
                    ->limit($item->qty)
                    ->get();
                
                foreach ($panels as $panel) {
                    $panelsToCancel[] = $panel->id;
                }
                
                // Use the current time in the tanggal field
                $this->stockController->recordSale(
                    $item->kode_barang,
                    $item->nama_barang,
                    $noTransaksi,
                    $currentDateTime, // Include time in the date field
                    $nota . ' (canceled)',
                    $supplierName . ' (' . $pembelian->kode_supplier . ')',
                    $item->qty, // Same quantity as purchase, but as a "sale" to reduce stock
                    'LBR', // 8th parameter should be $satuan (string)
                    'Transaction canceled: ' . $request->cancel_reason, // 9th param - keterangan
                    $canceledBy, // 10th param - created_by
                    $pembelian->cabang ?? 'default' // 11th param - so (stock owner)
                );
            }
            
            // Mark the panels as unavailable but do not delete them
            if (!empty($panelsToCancel)) {
                Log::info('Marking panels as unavailable:', ['panel_ids' => $panelsToCancel]);
                Panel::whereIn('id', $panelsToCancel)->update(['available' => false]);
            }
            
            // Update the purchase as canceled - include full timestamp
            $pembelian->update([
                'status' => 'canceled',
                'canceled_by' => $canceledBy,
                'canceled_at' => now(), // Stores full timestamp
                'cancel_reason' => $request->cancel_reason
            ]);
            
            DB::commit();
            
            return redirect()->route('pembelian.nota.list')
                ->with('success', 'Nota pembelian berhasil dibatalkan.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in pembelian cancel:', ['exception' => $e->getMessage()]);
            
            return redirect()->route('pembelian.nota.list')
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
    /**
 * Generate purchase transactions for all products for testing purposes.
 * THIS IS FOR DEVELOPMENT ONLY - DO NOT USE IN PRODUCTION
 */
    public function generateTestTransactions()
    {
        // Only allow in local/development environment
        if (!app()->environment('local', 'development')) {
            return 'This function is only available in development environment.';
        }

        try {
            DB::beginTransaction();
            
            // Get all kode barang items
            $kodeBarangItems = KodeBarang::where('status', 'Active')->get();
            
            if ($kodeBarangItems->isEmpty()) {
                return 'No active KodeBarang items found.';
            }
            
            // Get suppliers or create a default one if none exists
            $suppliers = Supplier::all();
            if ($suppliers->isEmpty()) {
                // Create a default supplier
                $supplier = Supplier::create([
                    'kode_supplier' => 'SUP-001',
                    'nama' => 'Supplier Test',
                    'alamat' => 'Jl. Test No. 123',
                    'telepon' => '08123456789',
                    'email' => 'supplier@test.com',
                ]);
                $suppliers = collect([$supplier]);
            }
            
            // Group items by attribute to create transactions for similar items
            $groupedItems = $kodeBarangItems->groupBy('attribute');
            
            $faker = \Faker\Factory::create('id_ID');
            $generatedCount = 0;
            
            // Find the highest nota number to avoid duplicates
            $currentMonth = date('m');
            $currentYear = date('y');
            $lastPurchase = Pembelian::where('nota', 'like', "BL/{$currentMonth}/{$currentYear}-%")
                                    ->orderBy('nota', 'desc')
                                    ->first();
            
            $notaNumber = 1;
            if ($lastPurchase) {
                // Extract the number part from the last nota
                $lastNotaParts = explode('-', $lastPurchase->nota);
                if (count($lastNotaParts) > 1) {
                    $notaNumber = (int)$lastNotaParts[1] + 1;
                }
            }
            
            // Create a transaction for each group of items (by attribute)
            foreach ($groupedItems as $attribute => $items) {
                // Skip processing if there are no items in this group
                if ($items->isEmpty()) {
                    continue;
                }
                
                // Generate transaction details with unique nota
                $nota = "BL/{$currentMonth}/{$currentYear}-" . str_pad($notaNumber, 5, '0', STR_PAD_LEFT);
                $notaNumber++;
                
                // Verify this nota doesn't already exist
                while (Pembelian::where('nota', $nota)->exists()) {
                    $nota = "BL/{$currentMonth}/{$currentYear}-" . str_pad($notaNumber, 5, '0', STR_PAD_LEFT);
                    $notaNumber++;
                }
                
                // Select a random supplier
                $supplier = $suppliers->random();
                
                // Use a date within the last 60 days
                $transactionDate = now()->subDays($faker->numberBetween(1, 60));
                
                // Create new transaction
                $subtotal = 0;
                $transactionItems = [];
                
                // Prepare items for this transaction
                foreach ($items as $item) {
                    // Random quantity between 1 and 5
                    $qty = $faker->numberBetween(1, 5);
                    // Use the item's cost as the purchase price
                    $harga = $item->cost;
                    // Calculate total
                    $total = $qty * $harga;
                    $subtotal += $total;
                    
                    $transactionItems[] = [
                        'kodeBarang' => $item->kode_barang,
                        'namaBarang' => $item->name,
                        'harga' => $harga,
                        'qty' => $qty,
                        'total' => $total,
                        'diskon' => 0,
                        'keterangan' => 'Auto-generated for testing'
                    ];
                }
                
                // Random discount between 0% and 5%
                $diskonPersen = $faker->randomFloat(2, 0, 5);
                $diskon = round($subtotal * ($diskonPersen / 100));
                
                // Random PPN between 0% and 11%
                $ppnPersen = $faker->randomFloat(2, 0, 11);
                $ppn = round(($subtotal - $diskon) * ($ppnPersen / 100));
                
                // Calculate grand total
                $grandTotal = $subtotal - $diskon + $ppn;
                
                // Payment methods
                $paymentMethods = ['Tunai', 'Transfer', 'Kredit'];
                $pembayaran = $faker->randomElement($paymentMethods);
                
                $surat_jalan = 'SJ-' . strtoupper($faker->bothify('??####'));
                
                // Create the purchase record
                $pembelian = Pembelian::create([
                    'nota' => $nota,
                    'no_surat_jalan' => $surat_jalan,
                    'tanggal' => $transactionDate,
                    'kode_supplier' => $supplier->kode_supplier,
                    'pembayaran' => $pembayaran,
                    'cara_bayar' => $pembayaran,
                    'subtotal' => $subtotal,
                    'diskon' => $diskon,
                    'ppn' => $ppn,
                    'grand_total' => $grandTotal,
                    'created_at' => $transactionDate,
                    'updated_at' => $transactionDate,
                ]);
                
                // Get creator name
                $creator = 'SYSTEM-TEST';
                
                // Format transaction number for mutation record
                $noTransaksi = "BL-" . $transactionDate->format('m/y') . "-" . 
                            substr($nota, strrpos($nota, '-') + 1) . 
                            " ({$creator})";
                
                // Create purchase items
                foreach ($transactionItems as $item) {
                    // Create purchase item
                    PembelianItem::create([
                        'nota' => $nota,
                        'kode_barang' => $item['kodeBarang'],
                        'nama_barang' => $item['namaBarang'],
                        'keterangan' => $item['keterangan'],
                        'harga' => $item['harga'],
                        'qty' => $item['qty'],
                        'diskon' => $item['diskon'],
                        'total' => $item['total'],
                        'created_at' => $transactionDate,
                        'updated_at' => $transactionDate,
                    ]);
                    
                    // Record purchase in stock mutation
                    $this->stockController->recordPurchase(
                        $item['kodeBarang'],
                        $item['namaBarang'],
                        $noTransaksi,
                        $transactionDate->format('Y-m-d H:i:s'),
                        $nota,
                        $supplier->nama . ' (' . $supplier->kode_supplier . ')',
                        $item['qty'],
                        'LBR',
                        'Auto-generated test transaction',
                        $creator,
                        'default'
                    );
                    
                    // Get the kode barang record for panel creation
                    $kodeBarang = KodeBarang::where('kode_barang', $item['kodeBarang'])->first();
                    if ($kodeBarang) {
                        // Add panels to inventory
                        $result = $this->panelController->addPanelsToInventory(
                            $item['namaBarang'],
                            $item['harga'], // cost
                            $item['kodeBarang'],
                            (int) $item['qty']
                        );
                        
                        $generatedCount += $item['qty'];
                    }
                }
            }
            
            DB::commit();
            
            return "Successfully generated " . count($groupedItems) . " purchase transactions with {$generatedCount} total items!";
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error generating test transactions:', ['exception' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return 'Error generating test transactions: ' . $e->getMessage();
        }
    }
    }