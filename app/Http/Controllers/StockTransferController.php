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
use App\Services\StockTransferService;
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

            // Normalize db keys for backward compatibility (e.g., DB1/DB2)
            $fromDb = $this->normalizeDatabaseKey($stockTransfer->from_database);
            $toDb   = $this->normalizeDatabaseKey($stockTransfer->to_database);

            \Log::info('Approving stock transfer', [
                'transfer_id' => $stockTransfer->id,
                'no_transfer' => $stockTransfer->no_transfer,
                'from' => $stockTransfer->from_database,
                'to' => $stockTransfer->to_database,
            ]);

            // Process stock transfer for each item
            foreach ($stockTransfer->items as $item) {
                \Log::info('Processing transfer item', [
                    'kode_barang' => $item->kode_barang,
                    'qty' => $item->qty_transfer,
                    'from' => $fromDb,
                    'to' => $toDb,
                ]);
                
                // Transfer stock using FIFO
                $this->transferStockWithFIFO($item, $fromDb, $toDb, $stockTransfer->no_transfer);

                // Create accounting entries
                $this->createAccountingEntries($stockTransfer, $item);
                \Log::info('Transfer item processed');
            }

            // Mark as completed
            $stockTransfer->update(['status' => 'completed']);

            DB::commit();

            return back()->with('success', 'Transfer stok berhasil disetujui dan diproses');

        } catch (Exception $e) {
            DB::rollback();
            \Log::error('Approve transfer failed', [
                'transfer_id' => $stockTransfer->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Map various DB aliases to configured keys
     */
    private function normalizeDatabaseKey(string $key): string
    {
        $k = strtolower(trim($key));
        if (in_array($k, ['db1', 'db_1', 'database1', 'primary', 'utama'])) return 'primary';
        if (in_array($k, ['db2', 'db_2', 'database2', 'secondary', 'kedua'])) return 'secondary';
        return $key; // fallback to original; MultiDatabaseTrait will validate
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

        // Wajib: gunakan satu akun transit intercompany (debit di DB1, kredit di DB2)
        $transitAccount = $this->findAccountByName('Hutang Transit');
        if (!$transitAccount) {
            $transitAccount = $this->findAccountByName('Hutang Transit Intercompany');
        }
        if (!$transitAccount) {
            $transitAccount = $this->findAccountByName('Hutang Transit (Intercompany Kewajiban)');
        }
        if (!$transitAccount) {
            throw new Exception('Akun "Hutang Transit" tidak ditemukan.');
        }

        // Journal 1 (DB1/source): Dr Transit / Cr Inventory Source
        $jr1 = Journal::create([
            'journal_no' => $transfer->no_transfer.'-SRC',
            'journal_date' => $transfer->tanggal_transfer,
            'description' => $desc.' [SOURCE]'
        ]);

        JournalDetail::create([
            'journal_id' => $jr1->id,
            'account_id' => $transitAccount->id,
            'debit' => $item->total_value,
            'credit' => 0,
            'memo' => 'Hutang Transit Intercompany (debit di DB1)'
        ]);
        JournalDetail::create([
            'journal_id' => $jr1->id,
            'account_id' => $inventoryAccountFrom->id,
            'debit' => 0,
            'credit' => $item->total_value,
            'memo' => 'Kredit Persediaan (keluar DB1)'
        ]);

        // Journal 2 (DB2/target): Dr Inventory Target / Cr Transit
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
            'memo' => 'Debit Persediaan (masuk DB2)'
        ]);
        JournalDetail::create([
            'journal_id' => $jr2->id,
            'account_id' => $transitAccount->id,
            'debit' => 0,
            'credit' => $item->total_value,
            'memo' => 'Hutang Transit Intercompany (kredit di DB2)'
        ]);
    }

    private function findAccountByName(string $name): ?ChartOfAccount
    {
        $acc = ChartOfAccount::whereRaw('LOWER(name) = ?', [mb_strtolower(trim($name))])->first();
        if ($acc) return $acc;
        // fallback contains
        $acc = ChartOfAccount::where('name', 'like', '%'.$name.'%')->first();
        return $acc;
    }

    /**
     * Transfer stock using FIFO method
     */
    private function transferStockWithFIFO(StockTransferItem $item, string $fromDb, string $toDb, string $transferNo = null)
    {
        try {
            // Get KodeBarang
            $kodeBarang = KodeBarang::where('kode_barang', $item->kode_barang)->first();
            if (!$kodeBarang) {
                throw new \Exception("KodeBarang {$item->kode_barang} not found");
            }

            // Convert database names to connection names
            $fromConnection = $fromDb === 'primary' ? 'mysql' : 'mysql_second';
            $toConnection = $toDb === 'primary' ? 'mysql' : 'mysql_second';

            // Use StockTransferService for FIFO transfer
            $stockTransferService = app(StockTransferService::class);
            
            $result = $stockTransferService->transferBetweenDatabases(
                $kodeBarang->id,
                $item->qty_transfer,
                $fromConnection,
                $toConnection,
                [
                    'unit' => $item->satuan ?? 'PCS',
                    'note' => "Transfer from {$fromDb} to {$toDb}" . ($transferNo ? " - {$transferNo}" : ''),
                    'created_by' => Auth::user()->name ?? 'SYSTEM'
                ]
            );

            // Update item with actual transferred quantity and average cost
            $item->update([
                'qty_transfer' => $result['qty'],
                'harga_per_unit' => $result['avg_cost'],
                'total_value' => $result['qty'] * $result['avg_cost']
            ]);

            \Log::info('FIFO transfer completed', [
                'kode_barang' => $item->kode_barang,
                'qty_transferred' => $result['qty'],
                'avg_cost' => $result['avg_cost'],
                'transfer_no' => $result['transfer_no']
            ]);

            return $result;

        } catch (\Exception $e) {
            \Log::error('FIFO transfer failed', [
                'kode_barang' => $item->kode_barang,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }
}