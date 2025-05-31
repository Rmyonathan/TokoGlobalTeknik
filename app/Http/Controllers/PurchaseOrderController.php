<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Customer;
use App\Models\Panel;
use App\Models\Transaksi;
use App\Models\TransaksiItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class PurchaseOrderController extends Controller
{
    protected $stockController;

    public function __construct(StockController $stockController)
    {
        $this->stockController = $stockController;
    }
 
    private function generateNoPO()
    {
        $now = now();
        $prefix = 'PO-' . $now->format('my'); // ex: PO-0625
        
        // Query yang works di SQLite dan MySQL
        $latestPO = PurchaseOrder::whereYear('tanggal', $now->year)
            ->whereMonth('tanggal', $now->month)
            ->orderBy('no_po', 'desc')
            ->first();

        $lastNumber = 0;
        
        if ($latestPO) {
            // ambil 5 digit terakhir
            $lastNumber = (int) substr($latestPO->no_po, -5);
        }

        $newNumber = str_pad($lastNumber + 1, 5, '0', STR_PAD_LEFT);
        return $prefix . '-' . $newNumber;
    }

    public function index(Request $request)
    {
        $query = PurchaseOrder::with(['items', 'customer']);

        // Search filters
        if ($request->filled('search_by') && $request->filled('search')) {
            $searchBy = $request->search_by;
            $keyword = $request->search;

            if ($searchBy === 'no_po') {
                $query->where('no_po', 'like', "%{$keyword}%");
            } elseif ($searchBy === 'kode_customer') {
                $query->whereHas('customer', function($q) use ($keyword) {
                    $q->where('kode_customer', 'like', "%{$keyword}%")
                    ->orWhere('nama', 'like', "%{$keyword}%");
                });
            } elseif ($searchBy === 'sales') {
                $query->where('sales', 'like', "%{$keyword}%");
            }
        }

        // Date range filter
        if ($request->filled('date_from')) {
            $query->whereDate('tanggal', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('tanggal', '<=', $request->date_to);
        }

        // Status filter
        if ($request->filled('status')) {
            if ($request->status === 'cancelled') {
                $query->where('status', 'like', 'cancelled%');
            } else {
                $query->where('status', $request->status);
            }
        }

        $purchaseOrders = $query->orderBy('tanggal', 'desc')
                            ->paginate(10)
                            ->withQueryString();

        return view('transaksi.purchaseorder', compact('purchaseOrders'));
    }

    public function show($id)
    {
        $po = PurchaseOrder::with(['items', 'customer'])->findOrFail($id);
        return view('transaksi.purchaseorder_detail', compact('po'));
    }

    public function store(Request $request)
    {
        Log::info('PO Store Request:', $request->all());
        
        try {
            // Parse items if they're sent as JSON string
            if ($request->has('items') && is_string($request->items)) {
                $parsedItems = json_decode($request->items, true);
                // Create a new request with the parsed items
                $request->merge(['items' => $parsedItems]);
            }
    
            // Validate the request
            $request->validate([
                'tanggal' => 'required|date',
                'kode_customer' => 'required|exists:customers,kode_customer',
                'sales' => 'required|exists:stok_owners,kode_stok_owner',
                'subtotal' => 'required|numeric',
                'grand_total' => 'required|numeric',
                'items' => 'required|array|min:1',
                'items.*.kodeBarang' => 'required|exists:panels,group_id',
                'items.*.harga' => 'required|numeric',
                'items.*.qty' => 'required|numeric|min:1',
            ]);
    
            DB::beginTransaction();
    
            Log::info('PO Validation passed');
    
            // Create purchase order
            $po = PurchaseOrder::create([
                'no_po' => $this->generateNoPO(),
                'tanggal' => $request->tanggal,
                'kode_customer' => $request->kode_customer,
                'sales' => $request->sales,
                'pembayaran' => $request->pembayaran,
                'cara_bayar' => $request->cara_bayar,
                'tanggal_jadi' => null,
                'subtotal' => $request->subtotal,
                'discount' => $request->discount ?? 0,
                'disc_rupiah' => $request->disc_rupiah ?? 0,
                'ppn' => $request->ppn,
                'dp' => $request->dp ?? 0,
                'grand_total' => $request->grand_total,
                'status' => 'pending',
                'is_edited' => false,
            ]);
    
            Log::info('PO Created:', ['po_id' => $po->id, 'no_po' => $po->no_po]);
    
            // Create PO items
            foreach ($request->items as $item) {
                Log::info('Processing item:', $item);
                
                $po->items()->create([
                    'kode_barang' => $item['kodeBarang'],
                    'nama_barang' => $item['namaBarang'],
                    'keterangan' => $item['keterangan'] ?? '',
                    'harga' => $item['harga'],
                    'panjang' => $item['panjang'] ?? 0,
                    'qty' => $item['qty'],
                    'total' => $item['total'],
                    'diskon' => $item['diskon'] ?? 0,
                ]);
            }
    
            DB::commit();
            Log::info('PO creation completed successfully');
    
            return response()->json([
                'status' => 'success', 
                'message' => 'Purchase Order created successfully.',
                'po_id' => $po->id,
                'no_po' => $po->no_po
            ]);
    
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('PO Creation Error:', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        Log::info('PO Update Request:', $request->all());
        
        try {
            // Validate the request
            $request->validate([
                'tanggal' => 'required|date',
                'kode_customer' => 'required|exists:customers,kode_customer',
                'sales' => 'required|exists:stok_owners,kode_stok_owner',
                'subtotal' => 'required|numeric',
                'grand_total' => 'required|numeric',
                'items' => 'required|array|min:1',
                'items.*.kodeBarang' => 'required|exists:panels,group_id',
                'items.*.harga' => 'required|numeric',
                'items.*.qty' => 'required|numeric|min:1',
                'edit_reason' => 'required|string|max:255',
            ]);
    
            DB::beginTransaction();
    
            // Find the PO
            $po = PurchaseOrder::with('items')->findOrFail($id);
            
            // Check if PO can be edited
            if ($po->status !== 'pending') {
                return redirect()->back()->with('error', 'Hanya PO dengan status pending yang bisa diedit.');
            }
    
            // Get the current user
            $editor = Auth::check() ? Auth::user()->name : 'ADMIN';
            $currentDateTime = now();
    
            // Update purchase order
            $po->update([
                'tanggal' => $request->tanggal,
                'kode_customer' => $request->kode_customer,
                'sales' => $request->sales,
                'pembayaran' => $request->pembayaran,
                'cara_bayar' => $request->cara_bayar,
                'subtotal' => $request->subtotal,
                'discount' => $request->discount ?? 0,
                'disc_rupiah' => $request->disc_rupiah ?? 0,
                'ppn' => $request->ppn,
                'dp' => $request->dp ?? 0,
                'grand_total' => $request->grand_total,
                'is_edited' => true,
                'edited_by' => $editor,
                'edited_at' => $currentDateTime,
                'edit_reason' => $request->edit_reason,
            ]);
    
            // Process items
            $existingItemIds = $po->items->pluck('id')->toArray();
            $updatedItemIds = [];
            
            foreach ($request->items as $itemData) {
                // Check if it's an existing item or a new one
                if (isset($itemData['id']) && $itemData['id'] !== 'new') {
                    // Update existing item
                    $item = PurchaseOrderItem::find($itemData['id']);
                    if ($item) {
                        $item->update([
                            'kode_barang' => $itemData['kodeBarang'],
                            'nama_barang' => $itemData['namaBarang'],
                            'keterangan' => $itemData['keterangan'] ?? '',
                            'harga' => $itemData['harga'],
                            'panjang' => $itemData['panjang'] ?? 0,
                            'qty' => $itemData['qty'],
                            'total' => $itemData['total'],
                            'diskon' => $itemData['diskon'] ?? 0,
                        ]);
                        $updatedItemIds[] = $item->id;
                    }
                } else {
                    // Create new item
                    $item = $po->items()->create([
                        'kode_barang' => $itemData['kodeBarang'],
                        'nama_barang' => $itemData['namaBarang'],
                        'keterangan' => $itemData['keterangan'] ?? '',
                        'harga' => $itemData['harga'],
                        'panjang' => $itemData['panjang'] ?? 0,
                        'qty' => $itemData['qty'],
                        'total' => $itemData['total'],
                        'diskon' => $itemData['diskon'] ?? 0,
                    ]);
                    $updatedItemIds[] = $item->id;
                }
            }
            
            // Delete items that are no longer in the updated PO
            $itemsToDelete = array_diff($existingItemIds, $updatedItemIds);
            if (!empty($itemsToDelete)) {
                PurchaseOrderItem::whereIn('id', $itemsToDelete)->delete();
            }
    
            DB::commit();
            Log::info('PO update completed successfully');
    
            return redirect()->route('purchase-order.show', ['id' => $po->id])
                ->with('success', 'Purchase Order berhasil diubah.');
    
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('PO Update Error:', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function cancel($id)
    {
        $po = PurchaseOrder::findOrFail($id);
        if ($po->status === 'pending') {
            $userName = Auth::user() ? Auth::user()->name : 'USER DEFAULT';
            $po->update(['status' => 'cancelled by ' . $userName]);
        }

        return redirect()->route('transaksi.purchaseorder')->with('success', 'PO dibatalkan.');
    }
    
    public function completeTransaction($id)
    {
        Log::info('CompleteTransaction started for PO ID: ' . $id);

        $po = PurchaseOrder::with('items', 'customer')->findOrFail($id);

        DB::beginTransaction();

        try {
            Log::info('PO Data:', $po->toArray());

            // Generate a new transaction number
            $lastTransaction = Transaksi::orderBy('created_at', 'desc')->first();
            $lastNumber = $lastTransaction ? (int) substr($lastTransaction->no_transaksi, strrpos($lastTransaction->no_transaksi, '/') + 1) : 0;
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
            $noTransaksi = 'KP/WS/' . $newNumber;
    
            // Get customer for stock mutation
            $customer = Customer::where('kode_customer', $po->kode_customer)->first();
            $customerName = $customer ? $customer->nama : 'Unknown Customer';
    
            // Format transaction number for stock mutation
            $creator = Auth::check() ? Auth::user()->name : 'ADMIN';
            $noTransaksiMutasi = $noTransaksi . " ({$creator})";
    
            // Create a new transaction
            $transaksi = Transaksi::create([
                'no_transaksi' => $noTransaksi,
                'tanggal' => now(),
                'kode_customer' => $po->kode_customer,
                'sales' => $po->sales,
                'pembayaran' => $po->pembayaran,
                'cara_bayar' => $po->cara_bayar,
                'tanggal_jadi' => now(),
                'subtotal' => $po->subtotal,
                'discount' => $po->discount,
                'disc_rupiah' => $po->disc_rupiah,
                'ppn' => $po->ppn,
                'dp' => $po->dp,
                'grand_total' => $po->grand_total,
                'status' => 'baru',
                'created_from_po' => $po->no_po,
                'is_edited' => $po->is_edited,
                'edited_by' => $po->edited_by,
                'edited_at' => $po->edited_at,
                'edit_reason' => $po->edit_reason,
            ]);
    
            // Create transaction items and record stock mutations
            foreach ($po->items as $item) {
                TransaksiItem::create([
                    'transaksi_id' => $transaksi->id,
                    'no_transaksi' => $noTransaksi,
                    'kode_barang' => $item->kode_barang,
                    'nama_barang' => $item->nama_barang,
                    'keterangan' => $item->keterangan ?? null,
                    'harga' => $item->harga,
                    'panjang' => $item->panjang ?? 0,
                    'lebar' => $item->lebar ?? 0,
                    'qty' => $item->qty,
                    'diskon' => $item->diskon ?? 0,
                    'total' => $item->total,
                ]);
    
                // Record the sale in stock mutation
                $this->stockController->recordSale(
                    $item->kode_barang,
                    $item->nama_barang,
                    $noTransaksiMutasi,
                    now(),
                    $noTransaksi,
                    $customerName . ' (' . $po->kode_customer . ')',
                    $item->qty,
                    $po->sales
                );
    
                // Update panel availability
                $panels = Panel::where('group_id', $item->kode_barang)
                    ->where('available', true)
                    ->limit($item->qty)
                    ->get();
    
                foreach ($panels as $panel) {
                    $panel->available = false;
                    $panel->save();
                }
            }
    
            // Update the status of the Purchase Order
            $po->update([
                'status' => 'completed',
                'tanggal_jadi' => now(),
            ]);
    
            DB::commit();
            Log::info('Transaction completed successfully'); // Debug log
    
            return redirect()->route('transaksi.listnota')->with('success', 'Transaksi berhasil diselesaikan.');

        } catch (\Exception $e) {
            Log::error('Error in completeTransaction: ' . $e->getMessage()); // Debug log
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}