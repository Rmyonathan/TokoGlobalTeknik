<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\StockTransfer;
use App\Models\StockTransferItem;
use App\Models\Stock;
use App\Models\KodeBarang;
use App\Models\ChartOfAccount;
use App\Models\Journal;
use App\Models\JournalDetail;
use App\Services\AccountingService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Exception;

class StockTransferController extends Controller
{
    /**
     * Display a listing of stock transfers
     */
    public function index(Request $request)
    {
        $query = StockTransfer::with(['creator', 'approver'])
            ->orderBy('created_at', 'desc');

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->filled('tanggal_awal')) {
            $query->where('tanggal_transfer', '>=', $request->tanggal_awal);
        }
        if ($request->filled('tanggal_akhir')) {
            $query->where('tanggal_transfer', '<=', $request->tanggal_akhir);
        }

        $transfers = $query->paginate(20);

        return view('stock-transfer.index', compact('transfers'));
    }

    /**
     * Show the form for creating a new stock transfer
     */
    public function create()
    {
        $products = KodeBarang::where('status', 'active')->get();
        $databases = [
            'primary' => 'Database Utama',
            'secondary' => 'Database Kedua'
        ];

        return view('stock-transfer.create', compact('products', 'databases'));
    }

    /**
     * Store a newly created stock transfer
     */
    public function store(Request $request)
    {
        $request->validate([
            'tanggal_transfer' => 'required|date',
            'from_database' => 'required|in:primary,secondary',
            'to_database' => 'required|in:primary,secondary',
            'items' => 'required|array|min:1',
            'items.*.kode_barang' => 'required|string',
            'items.*.qty_transfer' => 'required|numeric|min:0.01',
            'items.*.harga_per_unit' => 'required|numeric|min:0',
        ]);

        if ($request->from_database === $request->to_database) {
            return back()->withErrors(['error' => 'Database sumber dan tujuan tidak boleh sama']);
        }
        // dd($request);
        try {
            DB::beginTransaction();

            // Create stock transfer
            $transfer = StockTransfer::create([
                'no_transfer' => StockTransfer::generateTransferNumber(),
                'tanggal_transfer' => $request->tanggal_transfer,
                'from_database' => $request->from_database,
                'to_database' => $request->to_database,
                'keterangan' => $request->keterangan,
                'status' => 'pending',
                'created_by' => Auth::id(),
            ]);

            // Create transfer items
            foreach ($request->items as $item) {
                $product = KodeBarang::where('kode_barang', $item['kode_barang'])->first();
                
                if (!$product) {
                    throw new Exception("Produk {$item['kode_barang']} tidak ditemukan");
                }

                // Check stock availability
                $stockBreakdown = Stock::getStockBreakdown($item['kode_barang']);
                $availableStock = $stockBreakdown[$request->from_database]['good_stock'] ?? 0;

                if ($availableStock < $item['qty_transfer']) {
                    throw new Exception("Stok tidak mencukupi untuk {$product->name}. Stok tersedia: {$availableStock}");
                }

                StockTransferItem::create([
                    'stock_transfer_id' => $transfer->id,
                    'kode_barang' => $item['kode_barang'],
                    'nama_barang' => $product->name,
                    'qty_transfer' => $item['qty_transfer'],
                    'satuan' => $product->unit_dasar,
                    'harga_per_unit' => $item['harga_per_unit'],
                    'total_value' => $item['qty_transfer'] * $item['harga_per_unit'],
                    'keterangan' => $item['keterangan'] ?? null,
                ]);
            }

            DB::commit();

            return redirect()->route('stock-transfer.show', $transfer)
                ->with('success', 'Transfer stok berhasil dibuat dan menunggu persetujuan');

        } catch (Exception $e) {
            DB::rollback();
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }

    /**
     * Display the specified stock transfer
     */
    public function show(StockTransfer $stockTransfer)
    {
        $stockTransfer->load(['items', 'creator', 'approver']);
        return view('stock-transfer.show', compact('stockTransfer'));
    }

    /**
     * Approve stock transfer
     */
    public function approve(Request $request, StockTransfer $stockTransfer)
    {
        if ($stockTransfer->status !== 'pending') {
            return back()->withErrors(['error' => 'Transfer stok sudah diproses']);
        }

        try {
            DB::beginTransaction();

            // Update transfer status
            $stockTransfer->update([
                'status' => 'approved',
                'approved_by' => Auth::id(),
                'approved_at' => now(),
            ]);

            // Process stock transfer for each item
            foreach ($stockTransfer->items as $item) {
                // Transfer stock between databases
                Stock::transferStock(
                    $item->kode_barang,
                    $item->qty_transfer,
                    $stockTransfer->from_database,
                    $stockTransfer->to_database,
                    $item->harga_per_unit
                );

                // Create accounting entries
                $this->createAccountingEntries($stockTransfer, $item);
            }

            // Mark as completed
            $stockTransfer->update(['status' => 'completed']);

            DB::commit();

            return back()->with('success', 'Transfer stok berhasil disetujui dan diproses');

        } catch (Exception $e) {
            DB::rollback();
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Cancel stock transfer
     */
    public function cancel(Request $request, StockTransfer $stockTransfer)
    {
        if ($stockTransfer->status !== 'pending') {
            return back()->withErrors(['error' => 'Transfer stok sudah diproses']);
        }

        $stockTransfer->update([
            'status' => 'cancelled',
            'keterangan' => $request->keterangan_cancel ?? 'Dibatalkan'
        ]);

        return back()->with('success', 'Transfer stok berhasil dibatalkan');
    }

    /**
     * Get stock breakdown for a product
     */
    public function getStockBreakdown(Request $request)
    {
        $kodeBarang = $request->kode_barang;
        $breakdown = Stock::getStockBreakdown($kodeBarang);
        
        return response()->json([
            'success' => true,
            'data' => $breakdown
        ]);
    }

    /**
     * Get global stock for a product
     */
    public function getGlobalStock(Request $request)
    {
        $kodeBarang = $request->kode_barang;
        $globalStock = Stock::getGlobalStock($kodeBarang);
        
        return response()->json([
            'success' => true,
            'data' => $globalStock
        ]);
    }

    /**
     * Create accounting entries for stock transfer
     */
    private function createAccountingEntries(StockTransfer $transfer, StockTransferItem $item)
    {
        // Ambil akun langsung dari database berdasarkan NAMA standar
        $inventoryAccountFrom = $this->findAccountByName('Persediaan Barang Dagang');
        $inventoryAccountTo   = $this->findAccountByName('Persediaan Barang Dagang');

        if (!$inventoryAccountFrom || !$inventoryAccountTo) {
            throw new Exception('Akun "Persediaan Barang Dagang" tidak ditemukan');
        }

        $desc = "Transfer stok {$item->nama_barang} dari {$transfer->from_database} ke {$transfer->to_database}";

        // Default: gunakan skema in-transit intercompany
        $inTransitDr = $this->findAccountByName('Persediaan Transit (Intercompany Aset)');
        $inTransitCr = $this->findAccountByName('Hutang Transit (Intercompany Kewajiban)');

        if ($inTransitDr && $inTransitCr) {
            // Journal 1 (Source DB perspective): Dr In-Transit / Cr Inventory Source
            $jr1 = Journal::create([
                'journal_no' => $transfer->no_transfer.'-SRC',
                'journal_date' => $transfer->tanggal_transfer,
                'description' => $desc.' [SOURCE]'
            ]);

            JournalDetail::create([
                'journal_id' => $jr1->id,
                'account_id' => $inTransitDr->id,
                'debit' => $item->total_value,
                'credit' => 0,
                'memo' => 'Piutang in-transit transfer stok'
            ]);
            JournalDetail::create([
                'journal_id' => $jr1->id,
                'account_id' => $inventoryAccountFrom->id,
                'debit' => 0,
                'credit' => $item->total_value,
                'memo' => 'Pengiriman stok (keluar)'
            ]);

            // Journal 2 (Target DB perspective): Dr Inventory Target / Cr In-Transit
            $jr2 = Journal::create([
                'journal_no' => $transfer->no_transfer.'-DST',
                'journal_date' => $transfer->tanggal_transfer,
                'description' => $desc.' [TARGET]'
            ]);

            JournalDetail::create([
                'journal_id' => $jr2->id,
                'account_id' => $inventoryAccountTo->id,
                'debit' => $item->total_value,
                'credit' => 0,
                'memo' => 'Penerimaan stok (masuk)'
            ]);
            JournalDetail::create([
                'journal_id' => $jr2->id,
                'account_id' => $inTransitCr->id,
                'debit' => 0,
                'credit' => $item->total_value,
                'memo' => 'Hutang in-transit transfer stok'
            ]);
        } else {
            // Single journal: Dr Inventory Target / Cr Inventory Source
            $journal = Journal::create([
                'journal_no' => $transfer->no_transfer,
                'journal_date' => $transfer->tanggal_transfer,
                'description' => $desc,
            ]);

            JournalDetail::create([
                'journal_id' => $journal->id,
                'account_id' => $inventoryAccountTo->id,
                'debit' => $item->total_value,
                'credit' => 0,
                'memo' => 'Penerimaan stok (tanpa in-transit)'
            ]);
            JournalDetail::create([
                'journal_id' => $journal->id,
                'account_id' => $inventoryAccountFrom->id,
                'debit' => 0,
                'credit' => $item->total_value,
                'memo' => 'Pengiriman stok (tanpa in-transit)'
            ]);
        }
    }

    private function findAccountByName(string $name): ?ChartOfAccount
    {
        $acc = ChartOfAccount::whereRaw('LOWER(name) = ?', [mb_strtolower(trim($name))])->first();
        if ($acc) return $acc;
        // fallback contains
        $acc = ChartOfAccount::where('name', 'like', '%'.$name.'%')->first();
        return $acc;
    }
}