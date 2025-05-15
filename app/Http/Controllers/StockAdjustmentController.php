<?php

namespace App\Http\Controllers;

use App\Models\Stock;
use App\Models\StockAdjustment;
use App\Models\KodeBarang;
use App\Models\Panel;
use App\Models\StockMutation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StockAdjustmentController extends Controller
{
    protected $panelController;
    protected $stockController;

    public function __construct(
        ?PanelController $panelController = null,
        ?StockController $stockController = null
    )
    {
        $this->panelController = $panelController ?? app(PanelController::class);
        $this->stockController = $stockController ?? app(StockController::class);
    }

    /**
     * Display a listing of the stocks for adjustment.
     */
    public function index(Request $request)
    {
        $search = $request->input('search', '');
        $perPage = 10;
        
        // Get inventory using panel controller's method
        $inventory = $this->panelController->getKodeSummary($search, $perPage);
        
        return view('stock.adjustment.index', compact('inventory', 'search'));
    }

    /**
     * Display a listing of stock adjustments.
     */
    public function history(Request $request)
    {
        $search = $request->input('search', '');
        $perPage = 10;
        
        $adjustments = StockAdjustment::with(['stock', 'user'])
            ->when($search, function($query) use ($search) {
                return $query->whereHas('stock', function($q) use ($search) {
                    $q->where('kode_barang', 'like', "%{$search}%")
                      ->orWhere('nama_barang', 'like', "%{$search}%");
                })
                ->orWhere('keterangan', 'like', "%{$search}%");
            })
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
            
        return view('stock.adjustment.history', compact('adjustments', 'search'));
    }

    /**
     * Show the form for creating a new stock adjustment.
     */
    public function create()
    {
        $stocks = Stock::all();
        return view('stock.adjustment.create', compact('stocks'));
    }
    
    /**
     * Show form for adjusting specific item stock.
     */
    public function adjust($kodeBarang)
    {
        // Get the KodeBarang model by kode_barang
        $kodeBarang = KodeBarang::where('kode_barang', $kodeBarang)->firstOrFail();
        
        // Get current panel count for accurate stock value
        $currentPanelCount = Panel::where('group_id', $kodeBarang->kode_barang)
                                 ->where('available', true)
                                 ->count();
        
        // Get the stock record (or create it if it doesn't exist)
        $stock = Stock::where('kode_barang', $kodeBarang->kode_barang)->first();
        
        if (!$stock) {
            // If stock record doesn't exist, create one
            $stock = new Stock();
            $stock->kode_barang = $kodeBarang->kode_barang;
            $stock->nama_barang = $kodeBarang->name;
            $stock->good_stock = $currentPanelCount;
            $stock->save();
        } else {
            // Update stock to match actual panel count
            if ($stock->good_stock != $currentPanelCount) {
                $stock->good_stock = $currentPanelCount;
                $stock->save();
            }
        }
        
        return view('stock.adjustment.adjust', compact('stock', 'kodeBarang'));
    }

    /**
     * Store a newly created stock adjustment in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode_barang' => 'required|string|exists:stocks,kode_barang',
            'quantity_before' => 'required|integer',
            'quantity_after' => 'required|integer',
            'keterangan' => 'required|string|max:255',
        ]);

        $stock = Stock::where('kode_barang', $validated['kode_barang'])->first();
        
        if (!$stock) {
            return redirect()->back()->with('error', 'Stock tidak ditemukan.');
        }
        
        // Recheck current panel count to ensure accuracy
        $currentPanelCount = Panel::where('group_id', $validated['kode_barang'])
                                 ->where('available', true)
                                 ->count();
        
        // If actual panel count doesn't match the "before" quantity, update the validated data
        if ($currentPanelCount != $validated['quantity_before']) {
            $validated['quantity_before'] = $currentPanelCount;
        }
        
        $diff = $validated['quantity_after'] - $validated['quantity_before'];
        
        DB::beginTransaction();
        try {
            // Generate a unique transaction number for this adjustment
            $date = date('Ymd');
            $latestAdjustment = StockAdjustment::whereDate('created_at', today())->count() + 1;
            $adjustmentId = str_pad($latestAdjustment, 6, '0', STR_PAD_LEFT);
            $transactionNo = 'ADJ-' . $date . '-' . $adjustmentId;
            
            // First, check if there's already a mutation record with this transaction number
            $existingMutation = StockMutation::where('no_transaksi', $transactionNo)->first();
            if ($existingMutation) {
                // If it exists, generate a new unique ID to avoid duplication
                $transactionNo = 'ADJ-' . $date . '-' . $adjustmentId . '-' . uniqid();
            }
            
            // Create stock adjustment record
            $adjustment = StockAdjustment::create([
                'stock_id' => $stock->id,
                'kode_barang' => $stock->kode_barang,
                'quantity_before' => $validated['quantity_before'],
                'quantity_after' => $validated['quantity_after'],
                'difference' => $diff,
                'keterangan' => $validated['keterangan'],
                'user_id' => Auth::id(),
            ]);
            
            // IMPORTANT: We're bypassing the Stock update here because the recordPurchase/recordSale 
            // methods will handle the stock update internally
            
            // Record stock mutation based on the adjustment type (plus or minus)
            if ($diff > 0) {
                // This is an increase - record as a "purchase" in stock mutation
                $this->stockController->recordPurchase(
                    $stock->kode_barang,
                    $stock->nama_barang,
                    $transactionNo,
                    now()->format('Y-m-d H:i:s'),
                    $transactionNo,
                    'Stock Adjustment',
                    abs($diff),
                    $stock->satuan ?? 'LBR',
                    'Stock Adjustment: ' . $validated['keterangan'],
                    Auth::user()->name
                );
            } elseif ($diff < 0) {
                // This is a decrease - record as a "sale" in stock mutation
                $this->stockController->recordSale(
                    $stock->kode_barang,
                    $stock->nama_barang,
                    $transactionNo,
                    now()->format('Y-m-d H:i:s'),
                    $transactionNo,
                    'Stock Adjustment',
                    abs($diff),
                    $stock->satuan ?? 'LBR',
                    'Stock Adjustment: ' . $validated['keterangan'],
                    Auth::user()->name
                );
            } else {
                // No change in quantity, still need to update the original stock
                // This keeps the UI consistent when an adjustment with no change happens
                $stock->good_stock = $validated['quantity_after'];
                $stock->save();
            }
            
            // If this is panel inventory, we need to add/remove panels accordingly
            if ($diff != 0) {
                $this->adjustPanelInventory($stock->kode_barang, $diff);
            }
            
            DB::commit();
            
            return redirect()->route('stock.adjustment.index')
                ->with('success', 'Stock berhasil disesuaikan.');
                
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error in stock adjustment:', ['message' => $e->getMessage()]);
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified stock adjustment.
     */
    public function show($id)
    {
        $adjustment = StockAdjustment::with(['stock', 'user'])->findOrFail($id);
        
        // Get the stock mutation related to this adjustment for display
        // Use a more flexible search to find the mutation
        $mutation = StockMutation::where('supplier_customer', 'Stock Adjustment')
            ->where('keterangan', 'like', '%' . $adjustment->keterangan . '%')
            ->where('kode_barang', $adjustment->kode_barang)
            ->whereDate('tanggal', $adjustment->created_at->format('Y-m-d'))
            ->first();
        
        return view('stock.adjustment.show', compact('adjustment', 'mutation'));
    }
    
    /**
     * Adjust panel inventory based on difference
     */
    private function adjustPanelInventory($kodeBarang, $diff)
    {
        // Get the KodeBarang model
        $kodeBarangModel = KodeBarang::where('kode_barang', $kodeBarang)->first();
        
        if (!$kodeBarangModel) {
            throw new \Exception("Kode barang not found in master data");
        }
        
        if ($diff > 0) {
            // Need to add panels
            for ($i = 0; $i < $diff; $i++) {
                Panel::create([
                    'name' => $kodeBarangModel->name,
                    'cost' => $kodeBarangModel->cost,
                    'price' => $kodeBarangModel->price,
                    'length' => $kodeBarangModel->length,
                    'group_id' => $kodeBarangModel->kode_barang,
                    'available' => true,
                ]);
            }
        } else if ($diff < 0) {
            // Need to remove panels
            $panelsToRemove = abs($diff);
            
            // Get panels that are available
            $panels = Panel::where('group_id', $kodeBarang)
                        ->where('available', true)
                        ->orderBy('id')
                        ->limit($panelsToRemove)
                        ->get();
                        
            if (count($panels) < $panelsToRemove) {
                throw new \Exception("Tidak cukup panel tersedia untuk dihapus");
            }
            
            foreach ($panels as $panel) {
                $panel->delete();
            }
        }
    }
    
    /**
     * View stock mutation for a specific kode_barang
     */
    public function viewMutasi($kodeBarang)
    {
        return redirect()->route('stock.mutasi', [
            'kolom' => 'kode_barang',
            'value' => $kodeBarang,
            'selected_kode_barang' => $kodeBarang
        ]);
    }
}