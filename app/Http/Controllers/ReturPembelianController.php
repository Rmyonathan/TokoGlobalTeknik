<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\ReturPembelian;
use App\Models\ReturPembelianItem;
use App\Models\Pembelian;
use App\Models\KodeBarang;
use App\Models\PembelianItem;
use App\Models\Supplier;
use App\Models\NotaDebit;
use App\Services\FifoService;
use App\Services\AccountingService;

class ReturPembelianController extends Controller
{
    protected $fifoService;

    public function __construct(FifoService $fifoService)
    {
        $this->fifoService = $fifoService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $returPembelian = ReturPembelian::with(['supplier', 'pembelian', 'createdBy'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('retur_pembelian.index', compact('returPembelian'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $suppliers = Supplier::where('is_active', true)->get();
        $pembelians = Pembelian::with(['supplier', 'items'])
            ->where('status', '!=', 'canceled')
            ->orderBy('created_at', 'desc')
            ->limit(100)
            ->get();

        return view('retur_pembelian.create', compact('suppliers', 'pembelians'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'tanggal' => 'required|date',
            'kode_supplier' => 'required|exists:suppliers,kode_supplier',
            'pembelian_id' => 'required|exists:pembelian,id',
            'alasan_retur' => 'required|string|max:500',
            'items' => 'required|array|min:1',
            'items.*.pembelian_item_id' => 'required|exists:pembelian_items,id',
            'items.*.qty_retur' => 'required|numeric|min:0.01',
            'items.*.alasan' => 'nullable|string|max:255',
        ]);

        try {
            DB::beginTransaction();

            // Generate nomor retur
            $noRetur = $this->generateNoRetur();

            // Get pembelian data
            $pembelian = Pembelian::with(['supplier', 'items'])->findOrFail($request->pembelian_id);

            // Create retur pembelian
            $returPembelian = ReturPembelian::create([
                'no_retur' => $noRetur,
                'tanggal' => $request->tanggal,
                'kode_supplier' => $request->kode_supplier,
                'no_pembelian' => $pembelian->nota,
                'pembelian_id' => $request->pembelian_id,
                'total_retur' => 0, // Will be calculated
                'status' => 'pending',
                'alasan_retur' => $request->alasan_retur,
                'created_by' => Auth::id(),
            ]);

            $totalRetur = 0;

            // Create retur items
            foreach ($request->items as $item) {
                $pembelianItem = PembelianItem::findOrFail($item['pembelian_item_id']);
                
                // Validate qty retur doesn't exceed original qty
                if ($item['qty_retur'] > $pembelianItem->qty) {
                    throw new \Exception("Qty retur tidak boleh melebihi qty asli untuk item {$pembelianItem->nama_barang}");
                }

                $totalItem = $item['qty_retur'] * $pembelianItem->harga;
                $totalRetur += $totalItem;

                ReturPembelianItem::create([
                    'retur_pembelian_id' => $returPembelian->id,
                    'pembelian_item_id' => $item['pembelian_item_id'],
                    'kode_barang' => $pembelianItem->kode_barang,
                    'nama_barang' => $pembelianItem->nama_barang,
                    'qty_retur' => $item['qty_retur'],
                    'satuan' => $pembelianItem->satuan ?? 'Pcs',
                    'harga' => $pembelianItem->harga,
                    'total' => $totalItem,
                    'alasan' => $item['alasan'] ?? null,
                ]);
            }

            // Update total retur
            $returPembelian->update(['total_retur' => $totalRetur]);

            DB::commit();

            // Create accounting journal (DR Utang Usaha, CR Persediaan)
            try {
                app(AccountingService::class)->createJournalFromPurchaseReturn($returPembelian);
            } catch (\Exception $e) {
                Log::warning('Accounting journal for purchase return failed', ['message' => $e->getMessage(), 'no_retur' => $returPembelian->no_retur]);
            }

            return redirect()->route('retur-pembelian.index')
                ->with('success', 'Retur pembelian berhasil dibuat dengan nomor: ' . $noRetur);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating retur pembelian: ' . $e->getMessage());
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $returPembelian = ReturPembelian::with([
            'supplier', 
            'pembelian', 
            'items.pembelianItem', 
            'createdBy', 
            'approvedBy',
            'notaDebit'
        ])->findOrFail($id);

        return view('retur_pembelian.show', compact('returPembelian'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $returPembelian = ReturPembelian::with(['items'])->findOrFail($id);
        
        if ($returPembelian->status !== 'pending') {
            return redirect()->route('retur-pembelian.index')
                ->with('error', 'Retur yang sudah diproses tidak dapat diedit');
        }

        $supplier = Supplier::where('is_active', true)->get();
        $pembelians = Pembelian::with(['supplier', 'items'])
            ->where('status', '!=', 'canceled')
            ->orderBy('created_at', 'desc')
            ->limit(100)
            ->get();

        return view('retur_pembelian.edit', compact('returPembelian', 'supplier', 'pembelians'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $returPembelian = ReturPembelian::findOrFail($id);
        
        if ($returPembelian->status !== 'pending') {
            return redirect()->route('retur-pembelian.index')
                ->with('error', 'Retur yang sudah diproses tidak dapat diedit');
        }

        $request->validate([
            'tanggal' => 'required|date',
            'kode_supplier' => 'required|exists:supplier,kode_supplier',
            'pembelian_id' => 'required|exists:pembelian,id',
            'alasan_retur' => 'required|string|max:500',
            'items' => 'required|array|min:1',
            'items.*.pembelian_item_id' => 'required|exists:pembelian_items,id',
            'items.*.qty_retur' => 'required|numeric|min:0.01',
            'items.*.alasan' => 'nullable|string|max:255',
        ]);

        try {
            DB::beginTransaction();

            // Update retur pembelian
            $returPembelian->update([
                'tanggal' => $request->tanggal,
                'kode_supplier' => $request->kode_supplier,
                'pembelian_id' => $request->pembelian_id,
                'alasan_retur' => $request->alasan_retur,
            ]);

            // Delete existing items
            $returPembelian->items()->delete();

            $totalRetur = 0;

            // Create new retur items
            foreach ($request->items as $item) {
                $pembelianItem = PembelianItem::findOrFail($item['pembelian_item_id']);
                
                if ($item['qty_retur'] > $pembelianItem->qty) {
                    throw new \Exception("Qty retur tidak boleh melebihi qty asli untuk item {$pembelianItem->nama_barang}");
                }

                $totalItem = $item['qty_retur'] * $pembelianItem->harga;
                $totalRetur += $totalItem;

                ReturPembelianItem::create([
                    'retur_pembelian_id' => $returPembelian->id,
                    'pembelian_item_id' => $item['pembelian_item_id'],
                    'kode_barang' => $pembelianItem->kode_barang,
                    'nama_barang' => $pembelianItem->nama_barang,
                    'qty_retur' => $item['qty_retur'],
                    'satuan' => $pembelianItem->satuan ?? 'Pcs',
                    'harga' => $pembelianItem->harga,
                    'total' => $totalItem,
                    'alasan' => $item['alasan'] ?? null,
                ]);
            }

            // Update total retur
            $returPembelian->update(['total_retur' => $totalRetur]);

            DB::commit();

            return redirect()->route('retur-pembelian.index')
                ->with('success', 'Retur pembelian berhasil diperbarui');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating retur pembelian: ' . $e->getMessage());
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $returPembelian = ReturPembelian::findOrFail($id);
        
        if ($returPembelian->status !== 'pending') {
            return redirect()->route('retur-pembelian.index')
                ->with('error', 'Retur yang sudah diproses tidak dapat dihapus');
        }

        try {
            DB::beginTransaction();
            
            $returPembelian->items()->delete();
            $returPembelian->delete();
            
            DB::commit();

            return redirect()->route('retur-pembelian.index')
                ->with('success', 'Retur pembelian berhasil dihapus');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting retur pembelian: ' . $e->getMessage());
            
            return redirect()->route('retur-pembelian.index')
                ->with('error', 'Terjadi kesalahan saat menghapus retur pembelian');
        }
    }

    /**
     * Approve retur pembelian
     */
    public function approve(Request $request, string $id)
    {
        $returPembelian = ReturPembelian::with(['items'])->findOrFail($id);
        
        if ($returPembelian->status !== 'pending') {
            return redirect()->route('retur-pembelian.index')
                ->with('error', 'Retur sudah diproses sebelumnya');
        }

        try {
            DB::beginTransaction();

            // Update status to approved
            $returPembelian->update([
                'status' => 'approved',
                'approved_by' => Auth::id(),
                'approved_at' => now(),
            ]);

            // Create nota debit
            $noNotaDebit = $this->generateNoNotaDebit();
            
            NotaDebit::create([
                'no_nota_debit' => $noNotaDebit,
                'tanggal' => now()->toDateString(),
                'kode_supplier' => $returPembelian->kode_supplier,
                'retur_pembelian_id' => $returPembelian->id,
                'total_debit' => $returPembelian->total_retur,
                'sisa_nota_debit' => $returPembelian->total_retur, // Initialize sisa_nota_debit
                'keterangan' => "Nota debit untuk retur pembelian {$returPembelian->no_retur}",
                'status' => 'approved',
                'created_by' => Auth::id(),
                'approved_by' => Auth::id(),
                'approved_at' => now(),
            ]);

            // Update supplier payable (if exists)
            $supplier = Supplier::where('kode_supplier', $returPembelian->kode_supplier)->first();
            if ($supplier && isset($supplier->sisa_hutang)) {
                $supplier->decrement('sisa_hutang', $returPembelian->total_retur);
            }

            DB::commit();

            return redirect()->route('retur-pembelian.index')
                ->with('success', "Retur pembelian berhasil disetujui. Nota debit: {$noNotaDebit}");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error approving retur pembelian: ' . $e->getMessage());
            
            return redirect()->route('retur-pembelian.index')
                ->with('error', 'Terjadi kesalahan saat menyetujui retur pembelian');
        }
    }

    /**
     * Process retur pembelian (adjust stock)
     */
    public function process(string $id)
    {
        $returPembelian = ReturPembelian::with(['items'])->findOrFail($id);
        
        if ($returPembelian->status !== 'approved') {
            return redirect()->route('retur-pembelian.index')
                ->with('error', 'Retur harus disetujui terlebih dahulu');
        }

        try {
            DB::beginTransaction();

            // Process each item - reduce stock
            foreach ($returPembelian->items as $item) {
                // Get kode_barang ID from kode_barang string
                $kodeBarang = KodeBarang::where('kode_barang', $item->kode_barang)->first();
                if (!$kodeBarang) {
                    throw new \Exception("Kode barang {$item->kode_barang} tidak ditemukan");
                }
                
                // Reduce stock using FIFO service
                $this->fifoService->reduceStock(
                    $kodeBarang->id,
                    $item->qty_retur,
                    "Retur Pembelian {$returPembelian->no_retur}",
                    'retur_pembelian',
                    $returPembelian->id
                );
            }

            // Update status to processed
            $returPembelian->update(['status' => 'processed']);

            DB::commit();

            return redirect()->route('retur-pembelian.index')
                ->with('success', 'Retur pembelian berhasil diproses dan stok telah disesuaikan');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error processing retur pembelian: ' . $e->getMessage());
            
            return redirect()->route('retur-pembelian.index')
                ->with('error', 'Terjadi kesalahan saat memproses retur pembelian');
        }
    }

    /**
     * Reject retur pembelian
     */
    public function reject(Request $request, string $id)
    {
        $request->validate([
            'alasan_reject' => 'required|string|max:500',
        ]);

        $returPembelian = ReturPembelian::findOrFail($id);
        
        if ($returPembelian->status !== 'pending') {
            return redirect()->route('retur-pembelian.index')
                ->with('error', 'Retur sudah diproses sebelumnya');
        }

        try {
            $returPembelian->update([
                'status' => 'rejected',
                'alasan_retur' => $returPembelian->alasan_retur . "\n\nAlasan ditolak: " . $request->alasan_reject,
            ]);

            return redirect()->route('retur-pembelian.index')
                ->with('success', 'Retur pembelian berhasil ditolak');

        } catch (\Exception $e) {
            Log::error('Error rejecting retur pembelian: ' . $e->getMessage());
            
            return redirect()->route('retur-pembelian.index')
                ->with('error', 'Terjadi kesalahan saat menolak retur pembelian');
        }
    }

    /**
     * Get pembelian items for AJAX
     */
    public function getPembelianItems(Request $request)
    {
        $pembelianId = $request->get('pembelian_id');
        
        if (!$pembelianId) {
            return response()->json(['error' => 'Pembelian ID required'], 400);
        }

        // Get the pembelian to get the nota
        $pembelian = Pembelian::find($pembelianId);
        if (!$pembelian) {
            return response()->json(['error' => 'Pembelian not found'], 404);
        }

        $items = PembelianItem::where('nota', $pembelian->nota)
            ->with('kodeBarang')
            ->get()
            ->map(function ($item) {
                // Hitung qty tersisa setelah retur
                $qtyTersisa = \App\Models\ReturPembelianItem::getQtyTersisaForPembelianItem($item->id);
                
                return [
                    'id' => $item->id,
                    'kode_barang' => $item->kode_barang,
                    'nama_barang' => $item->nama_barang,
                    'qty' => $qtyTersisa, // Gunakan qty tersisa, bukan qty asli
                    'qty_asli' => $item->qty, // Simpan qty asli untuk referensi
                    'satuan' => $item->satuan ?? 'Pcs',
                    'harga' => $item->harga,
                    'total' => $item->total,
                ];
            });

        return response()->json(['items' => $items]);
    }

    /**
     * Generate nomor retur
     */
    private function generateNoRetur()
    {
        $year = date('Y');
        $month = date('m');
        
        $lastRetur = ReturPembelian::whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->orderBy('created_at', 'desc')
            ->first();

        if ($lastRetur) {
            $lastNumber = (int) substr($lastRetur->no_retur, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return "RB-{$year}{$month}-" . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Generate nomor nota debit
     */
    private function generateNoNotaDebit()
    {
        $year = date('Y');
        $month = date('m');
        
        $lastNota = NotaDebit::whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->orderBy('created_at', 'desc')
            ->first();

        if ($lastNota) {
            $lastNumber = (int) substr($lastNota->no_nota_debit, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return "ND-{$year}{$month}-" . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }
}
