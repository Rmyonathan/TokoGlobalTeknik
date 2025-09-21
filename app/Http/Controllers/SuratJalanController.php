<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SuratJalan;
use App\Models\SuratJalanItem;
use App\Models\SuratJalanItemSumber;
use App\Models\TransaksiItemSumber;
use App\Models\Customer;
use App\Models\Transaksi;
use App\Models\TransaksiItem;
use App\Models\StockBatch;
use App\Models\KodeBarang;
use App\Models\CaraBayar;
use App\Models\StokOwner;
use App\Services\FifoService;
use App\Services\UnitConversionService;
use App\Services\PoNumberGeneratorService;
use Illuminate\Support\Facades\DB;
use Exception;

use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class SuratJalanController extends Controller
{
    protected $fifoService;
    protected $unitService;

    public function __construct(FifoService $fifoService, UnitConversionService $unitService)
    {
        $this->fifoService = $fifoService;
        $this->unitService = $unitService;
    }

    public function create(Request $request)
    {
        $lastSuratJalan = SuratJalan::orderBy('created_at', 'desc')->first();
        $newNumber = $lastSuratJalan ? ((int) substr($lastSuratJalan->no_suratjalan, -5)) + 1 : 1;
        $noSuratJalan = 'SJ-' . date('m') . date('y') . '-' . str_pad($newNumber, 5, '0', STR_PAD_LEFT);

        // Get all transactions that can be used for Surat Jalan
        // Only show transactions that haven't been used for Surat Jalan yet
        $availableTransactions = Transaksi::with(['customer', 'items'])
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('surat_jalan')
                    ->whereColumn('surat_jalan.no_transaksi', 'transaksi.no_transaksi');
            })
            ->orderBy('created_at', 'desc')
            ->get();

        $noTransaksi = $request->get('no_transaksi'); // Jika ada no_transaksi dari query string
        $transaksi = $noTransaksi ? Transaksi::with(['items', 'customer'])->where('no_transaksi', $noTransaksi)->first() : null;
        $transaksiItems = $transaksi ? $transaksi->items : collect();

        // Get kode barangs for manual item addition
        $kodeBarangs = KodeBarang::orderBy('kode_barang')->get();
        
        // Get customers for dropdown
        $customers = Customer::orderBy('nama')->get();
        
        // Get PPN configuration from company settings
        $ppnConfig = \App\Services\PpnService::getPpnConfig();

        // Cara Bayar master
        $caraBayars = CaraBayar::orderBy('metode')->orderBy('nama')->get();

        return view('suratjalan.suratjalan', compact('noSuratJalan', 'availableTransactions', 'transaksi', 'transaksiItems', 'kodeBarangs', 'customers', 'ppnConfig', 'caraBayars'));
    }

    public function store(Request $request){
        // Debug logging
        Log::info('Surat Jalan Store Request:', [
            'request_data' => $request->all(),
            'items_count' => count($request->items ?? []),
            'items' => $request->items
        ]);
        
        try {
        $request->validate([
            'no_suratjalan' => 'required|unique:surat_jalan,no_suratjalan',
            'tanggal' => 'required|date',
            'kode_customer' => 'required|exists:customers,kode_customer',
            'alamat_suratjalan' => 'nullable|string',
            // Untuk alur SJ -> Faktur, no_transaksi boleh kosong
            'no_transaksi' => 'nullable|string',
            'no_po' => 'nullable|string|max:50',
            'tanggal_transaksi' => 'nullable|date',
            'titipan_uang' => 'nullable|numeric',
            'sisa_piutang' => 'nullable|numeric',
            'metode_pembayaran' => 'nullable|string',
            'cara_bayar' => 'nullable|string',
            'hari_tempo' => 'nullable|integer|min:0',
            'tanggal_jatuh_tempo' => 'nullable|date',
            'items' => 'required|array|min:1',
            // Tidak wajib refer ke transaksi saat SJ dibuat lebih dulu
            'items.*.kode_barang' => 'required|string',
                'items.*.nama_barang' => 'required|string',
                'items.*.qty' => 'required|numeric|min:0.01',
                'items.*.satuan' => 'required|string',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Surat Jalan Validation Error:', [
                'errors' => $e->errors(),
                'request_data' => $request->all()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        }
    
        DB::beginTransaction();
        try {
            // Use PO number from input (no auto-generate)
            $noPo = $request->no_po;

            $suratJalan = SuratJalan::create([
                'no_suratjalan' => $request->no_suratjalan,
                'tanggal' => $request->tanggal ?? now(),
                'kode_customer' => $request->kode_customer,
                'alamat_suratjalan' => $request->alamat_suratjalan ?? "default",
                // Boleh null pada alur SJ -> Faktur
                'no_transaksi' => $request->no_transaksi,
                'no_po' => $noPo,
                'tanggal_transaksi' => $request->tanggal_transaksi ?? $request->tanggal,
                'titipan_uang' => $request->titipan_uang ?? 0,
                'sisa_piutang' => $request->sisa_piutang ?? 0,
                'metode_pembayaran' => $request->metode_pembayaran ?? 'Non Tunai',
                'cara_bayar' => $request->cara_bayar ?? 'Kredit',
                'hari_tempo' => $request->hari_tempo ?? 0,
                'tanggal_jatuh_tempo' => $request->tanggal_jatuh_tempo,
            ]);

            foreach ($request->items as $item) {
                // Cari kode barang untuk mendapatkan ID
                $kodeBarang = KodeBarang::where('kode_barang', $item['kode_barang'])->first();
                if (!$kodeBarang) {
                    throw new Exception("Kode barang {$item['kode_barang']} tidak ditemukan");
                }

                // Konversi qty ke unit dasar untuk validasi stok
                $qtyInBaseUnit = $this->unitService->convertToBaseUnit(
                    $kodeBarang->id, 
                    $item['qty'], 
                    $item['satuan']
                );

                // Validasi stok tersedia
                $stokTersedia = $this->fifoService->getStokTersedia($kodeBarang->id);
                if ($stokTersedia < $qtyInBaseUnit) {
                    throw new Exception("Stok tidak mencukupi untuk {$item['nama_barang']}. Tersedia: {$stokTersedia}, Dibutuhkan: {$qtyInBaseUnit}");
                }

                // Buat Surat Jalan Item tanpa keterikatan wajib ke transaksi item
                $suratJalanItem = SuratJalanItem::create([
                    'no_suratjalan' => $suratJalan->no_suratjalan,
                    // transaksi_id boleh null untuk alur SJ -> Faktur
                    'transaksi_id' => $item['transaksi_id'] ?? null,
                    'kode_barang' => $item['kode_barang'],
                    'nama_barang' => $item['nama_barang'],
                    'qty' => $item['qty'],
                    'satuan' => $item['satuan'],
                    'satuan_besar' => $item['satuan_besar'] ?? null
                ]);

                // Lakukan alokasi FIFO untuk Surat Jalan
                $alokasiResult = $this->fifoService->alokasiStokUntukSuratJalan($kodeBarang->id, $qtyInBaseUnit, $suratJalanItem->id);

                // Catat alokasi untuk Surat Jalan (bukan untuk Transaksi Item)
                foreach ($alokasiResult['alokasi'] as $alokasi) {
                    SuratJalanItemSumber::create([
                        'surat_jalan_item_id' => $suratJalanItem->id,
                        'stock_batch_id' => $alokasi['batch_id'],
                        'qty_diambil' => $alokasi['qty_ambil'],
                        'harga_modal' => $alokasi['harga_modal']
                    ]);

                    Log::info('FIFO Allocation', [
                    'no_suratjalan' => $suratJalan->no_suratjalan,
                    'kode_barang'   => $item['kode_barang'],
                    'nama_barang'   => $item['nama_barang'],
                    'batch_id'      => $alokasi['batch_id'],
                    'qty_diambil'   => $alokasi['qty_ambil'],
                    'harga_modal'   => $alokasi['harga_modal']
                ]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Surat Jalan berhasil disimpan dengan alokasi FIFO!', 
                'id' => $suratJalan->id,
                'no_suratjalan'=> $suratJalan->no_suratjalan,
                'no_transaksi' => $suratJalan->no_transaksi,
                'tanggal' => $suratJalan->tanggal,
                'kode_customer' => $suratJalan->kode_customer,
                'alamat_suratjalan' => $suratJalan->alamat_suratjalan,
                'grand_total' => $request->grand_total ?? 0
            ]);

        } catch (Exception $e) {
            DB::rollBack();
            
            // Log detailed error information
            Log::error('Surat Jalan Store Error:', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Terjadi kesalahan: ' . $e->getMessage(),
                'details' => [
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ]
            ], 400);
        }
    }

    public function history(Request $request){
        $query = SuratJalan::with('customer', 'items.transaksiItem');

        if ($request->filled('search')) {
            $search = $request->search;
            $searchBy = $request->search_by ?? 'no_suratjalan';
            if ($searchBy == 'customer') {
                $query->whereHas('customer', function($q) use ($search) {
                    $q->where('nama', 'like', "%$search%");
                });
            } elseif ($searchBy == 'alamat_suratjalan') {
                $query->where('alamat_suratjalan', 'like', "%$search%");
            } else {
                $query->where($searchBy, 'like', "%$search%");
            }
        }

        if ($request->filled('start_date')) {
            $query->where('tanggal', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->where('tanggal', '<=', $request->end_date);
        }

        // FIX: Replaced withQueryString() with appends() to avoid linter errors.
        // This keeps the filter and search parameters in the pagination links.
        $suratJalan = $query->latest('created_at')->paginate(10)->appends($request->query());

        return view('suratjalan.historysuratjalan', compact('suratJalan'));
    }

    public function detail($id)
    {
        // FIX: Added 'transaksi.items' to ensure all data is available for the view,
        // which prevents potential errors if the view accesses it.
        $suratJalan = SuratJalan::with(['items', 'customer', 'transaksi.items'])->findOrFail($id);

        // Kumpulkan SJ lain yang memiliki no_transaksi yang sama (saudara satu faktur)
        $relatedSjNumbers = collect();
        $relatedSjs = collect();
        if (!empty($suratJalan->no_transaksi)) {
            $relatedSjs = SuratJalan::with(['items'])
                ->where('no_transaksi', $suratJalan->no_transaksi)
                ->where('id', '<>', $suratJalan->id)
                ->orderBy('tanggal')
                ->get();
            $relatedSjNumbers = $relatedSjs->pluck('no_suratjalan');
        }

        return view('suratjalan.detail', compact('suratJalan', 'relatedSjNumbers', 'relatedSjs'));
    }

    /**
     * Get available stock for a specific product
     */
    public function getAvailableStock(Request $request)
    {
        $request->validate([
            'kode_barang' => 'required|string',
            'satuan' => 'required|string',
        ]);

        try {
            $kodeBarang = KodeBarang::where('kode_barang', $request->kode_barang)->first();
            if (!$kodeBarang) {
                return response()->json(['error' => 'Kode barang tidak ditemukan'], 404);
            }

            $stokTersedia = $this->fifoService->getStokTersedia($kodeBarang->id);
            $stokInRequestedUnit = $this->unitService->convertFromBaseUnit(
                $kodeBarang->id, 
                $stokTersedia, 
                $request->satuan
            );

            return response()->json([
                'stok_tersedia' => $stokTersedia,
                'stok_dalam_satuan' => $stokInRequestedUnit,
                'satuan' => $request->satuan
            ]);

        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Get available units for a specific product
     */
    public function getAvailableUnits($kodeBarangId)
    {
        try {
            $units = $this->unitService->getAvailableUnits($kodeBarangId);
            return response()->json($units);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Get FIFO allocation details for a specific Surat Jalan Item
     */
    public function getFifoAllocation($suratJalanItemId)
    {
        try {
            $suratJalanItem = SuratJalanItem::with(['suratJalanItemSumber.stockBatch.pembelianItem.pembelian.supplierRelation'])
                ->findOrFail($suratJalanItemId);

            $allocationDetails = [];
            foreach ($suratJalanItem->suratJalanItemSumber as $sumber) {
                $allocationDetails[] = [
                    'batch_number' => $sumber->stockBatch->batch_number,
                    'qty_diambil' => $sumber->qty_diambil,
                    'harga_modal' => $sumber->harga_modal,
                    'supplier' => $sumber->stockBatch->pembelianItem->pembelian->supplierRelation->nama ?? 'Unknown',
                    'tanggal_masuk' => $sumber->stockBatch->tanggal_masuk->format('d/m/Y')
                ];
            }

            return response()->json([
                'surat_jalan_item' => $suratJalanItem,
                'allocation_details' => $allocationDetails
            ]);

        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * API: Get Surat Jalan by number, including items and default prices
     */
    public function apiByNo(string $no)
    {
        $sj = SuratJalan::with(['items', 'customer'])
            ->where('no_suratjalan', $no)
            ->first();

        if (!$sj) {
            return response()->json(['error' => 'Surat Jalan tidak ditemukan'], 404);
        }

        $items = [];
        foreach ($sj->items as $it) {
            $kb = KodeBarang::where('kode_barang', $it->kode_barang)->first();
            $items[] = [
                'surat_jalan_item_id' => $it->id,
                'kode_barang' => $it->kode_barang,
                'nama_barang' => $it->nama_barang,
                'qty' => $it->qty,
                'satuan' => $it->satuan ?? $kb?->unit_dasar ?? 'PCS', // Satuan kecil dari surat jalan
                'satuan_besar' => $it->satuan_besar ?? '', // Satuan besar dari surat jalan
                'harga_jual_default' => (float) ($kb?->harga_jual ?? 0),
                'unit_dasar' => $kb?->unit_dasar ?? 'PCS',
                'kode_barang_id' => $kb?->id,
                'merek' => $kb?->merek,
                'ukuran' => $kb?->ukuran,
            ];
        }

        return response()->json([
            'id' => $sj->id,
            'no_suratjalan' => $sj->no_suratjalan,
            'no_transaksi' => $sj->no_transaksi,
            'no_po' => $sj->no_po,
            'tanggal' => $sj->tanggal,
            'metode_pembayaran' => $sj->metode_pembayaran,
            'cara_bayar' => $sj->cara_bayar,
            'hari_tempo' => $sj->hari_tempo,
            'tanggal_jatuh_tempo' => $sj->tanggal_jatuh_tempo,
            'customer' => [
                'kode_customer' => $sj->kode_customer,
                'nama' => $sj->customer->nama ?? null,
                'alamat' => $sj->customer->alamat ?? null,
                'hp' => $sj->customer->hp ?? null,
                'telepon' => $sj->customer->telepon ?? null,
            ],
            'items' => $items,
        ]);
    }
    /**
     * Batalkan Surat Jalan: rollback alokasi FIFO, kurangi qty_terkirim SO, dan set status canceled
     */
    public function cancel(Request $request, $id)
    {
        $request->validate([
            'cancel_reason' => 'required|string|max:255',
        ]);

        DB::beginTransaction();
        try {
            $sj = SuratJalan::with(['items.suratJalanItemSumber', 'items.transaksiItem'])->findOrFail($id);

            if ($sj->status === 'canceled') {
                return back()->with('error', 'Surat Jalan sudah dibatalkan.');
            }

            // Kembalikan alokasi FIFO ke stock_batches
            foreach ($sj->items as $item) {
                foreach ($item->suratJalanItemSumber as $src) {
                    $batch = StockBatch::find($src->stock_batch_id);
                    if ($batch) {
                        $batch->qty_sisa += $src->qty_diambil;
                        $batch->save();
                    }
                    $src->delete();
                }
            }

            // Kurangi qty_terkirim pada sales order items jika ada relasi
            if (!empty($sj->no_transaksi)) {
                try {
                    $trx = Transaksi::with(['salesOrder.items'])->where('no_transaksi', $sj->no_transaksi)->first();
                    if ($trx && $trx->salesOrder) {
                        foreach ($sj->items as $item) {
                            $soItem = $trx->salesOrder->items->firstWhere('kode_barang', $item->kode_barang);
                            if ($soItem && isset($soItem->qty_terkirim)) {
                                $soItem->qty_terkirim = max(0, ($soItem->qty_terkirim - $item->qty));
                                $soItem->save();
                            }
                        }
                    }
                } catch (\Exception $e) {
                    Log::warning('Revert SO qty on SJ cancel failed', ['message' => $e->getMessage()]);
                }
            }

            $sj->status = 'canceled';
            $sj->keterangan = trim(($sj->keterangan ?? '') . ' | DIBATALKAN: ' . $request->cancel_reason);
            $sj->save();

            DB::commit();
            return back()->with('success', 'Surat Jalan berhasil dibatalkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal membatalkan Surat Jalan: ' . $e->getMessage());
        }
    }

    /**
     * Rekap Surat Jalan dengan statistik dan ringkasan
     */
    public function rekap(Request $request)
    {
        $query = SuratJalan::with(['customer', 'items'])
            ->selectRaw('
                surat_jalan.id,
                surat_jalan.no_suratjalan,
                surat_jalan.tanggal,
                surat_jalan.kode_customer,
                surat_jalan.no_po,
                surat_jalan.no_transaksi,
                surat_jalan.alamat_suratjalan,
                surat_jalan.titipan_uang,
                surat_jalan.sisa_piutang,
                surat_jalan.metode_pembayaran,
                surat_jalan.cara_bayar,
                surat_jalan.hari_tempo,
                surat_jalan.tanggal_jatuh_tempo,
                surat_jalan.created_at,
                surat_jalan.updated_at,
                (SELECT COUNT(*) FROM surat_jalan_items WHERE surat_jalan_items.no_suratjalan = surat_jalan.no_suratjalan) as total_items,
                (SELECT COALESCE(SUM(qty), 0) FROM surat_jalan_items WHERE surat_jalan_items.no_suratjalan = surat_jalan.no_suratjalan) as total_qty
            ');

        // Filter berdasarkan tanggal
        if ($request->filled('start_date')) {
            $query->where('surat_jalan.tanggal', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->where('surat_jalan.tanggal', '<=', $request->end_date);
        }

        // Filter berdasarkan customer
        if ($request->filled('customer_id')) {
            $query->where('surat_jalan.kode_customer', $request->customer_id);
        }

        // Filter berdasarkan status
        if ($request->filled('status')) {
            if ($request->status === 'sudah_faktur') {
                $query->whereNotNull('surat_jalan.no_transaksi');
            } elseif ($request->status === 'belum_faktur') {
                $query->whereNull('surat_jalan.no_transaksi');
            }
        }

        // Filter berdasarkan pencarian
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('surat_jalan.no_suratjalan', 'like', "%{$search}%")
                  ->orWhere('surat_jalan.no_po', 'like', "%{$search}%")
                  ->orWhereHas('customer', function($customerQuery) use ($search) {
                      $customerQuery->where('nama', 'like', "%{$search}%");
                  });
            });
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'tanggal');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy("surat_jalan.{$sortBy}", $sortOrder);

        // Pagination
        $suratJalan = $query->paginate(20)->appends($request->query());

        // Statistik
        $stats = $this->getSuratJalanStats($request);

        // Data untuk chart
        $chartData = $this->getChartData($request);

        // Data customer untuk filter
        $customers = Customer::orderBy('nama')->get();

        return view('suratjalan.rekap', compact('suratJalan', 'stats', 'chartData', 'customers'));
    }

    /**
     * Get statistics for surat jalan
     */
    private function getSuratJalanStats(Request $request)
    {
        $query = SuratJalan::query();

        // Apply same filters as main query
        if ($request->filled('start_date')) {
            $query->where('tanggal', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->where('tanggal', '<=', $request->end_date);
        }
        if ($request->filled('customer_id')) {
            $query->where('kode_customer', $request->customer_id);
        }

        $totalSuratJalan = $query->count();
        $sudahFaktur = $query->clone()->whereNotNull('no_transaksi')->count();
        $belumFaktur = $query->clone()->whereNull('no_transaksi')->count();

        // Total quantity dari items
        $totalQty = $query->clone()
            ->join('surat_jalan_items', 'surat_jalan.no_suratjalan', '=', 'surat_jalan_items.no_suratjalan')
            ->sum('surat_jalan_items.qty') ?? 0;

        // Top customers
        $topCustomers = $query->clone()
            ->join('customers', 'surat_jalan.kode_customer', '=', 'customers.kode_customer')
            ->selectRaw('customers.nama, COUNT(*) as total_sj')
            ->groupBy('customers.kode_customer', 'customers.nama')
            ->orderBy('total_sj', 'desc')
            ->limit(5)
            ->get();

        return [
            'total_surat_jalan' => $totalSuratJalan,
            'sudah_faktur' => $sudahFaktur,
            'belum_faktur' => $belumFaktur,
            'total_qty' => $totalQty,
            'persentase_sudah_faktur' => $totalSuratJalan > 0 ? round(($sudahFaktur / $totalSuratJalan) * 100, 2) : 0,
            'top_customers' => $topCustomers
        ];
    }

    /**
     * Get chart data for surat jalan
     */
    private function getChartData(Request $request)
    {
        $query = SuratJalan::query();

        // Apply same filters as main query
        if ($request->filled('start_date')) {
            $query->where('tanggal', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->where('tanggal', '<=', $request->end_date);
        }
        if ($request->filled('customer_id')) {
            $query->where('kode_customer', $request->customer_id);
        }

        // Chart 1: Surat Jalan per hari (7 hari terakhir)
        $dailyData = $query->clone()
            ->selectRaw('DATE(tanggal) as date, COUNT(*) as total')
            ->where('tanggal', '>=', now()->subDays(7))
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->pluck('total', 'date')
            ->toArray();

        // Chart 2: Status surat jalan
        $statusData = [
            'Sudah Faktur' => $query->clone()->whereNotNull('no_transaksi')->count(),
            'Belum Faktur' => $query->clone()->whereNull('no_transaksi')->count()
        ];

        // Chart 3: Top 5 customers
        $customerData = $query->clone()
            ->join('customers', 'surat_jalan.kode_customer', '=', 'customers.kode_customer')
            ->selectRaw('customers.nama, COUNT(*) as total')
            ->groupBy('customers.kode_customer', 'customers.nama')
            ->orderBy('total', 'desc')
            ->limit(5)
            ->get()
            ->pluck('total', 'nama')
            ->toArray();

        return [
            'daily' => $dailyData,
            'status' => $statusData,
            'customers' => $customerData
        ];
    }

    /**
     * Display the form to create invoice from multiple delivery orders
     */
    public function createMultipleFaktur()
    {
        // Get delivery orders that are ready for invoicing (not yet converted to invoice)
        $suratJalans = SuratJalan::with(['customer', 'items'])
            ->whereNull('no_transaksi') // Not yet converted to invoice
            ->orderBy('tanggal', 'desc')
            ->get()
            ->groupBy('kode_customer'); // Group by customer

        $salesList = StokOwner::orderBy('keterangan')->get(['kode_stok_owner', 'keterangan']);

        return view('transaksi.create_from_multiple_sj', compact('suratJalans', 'salesList'));
    }

    /**
     * Get delivery orders for a specific customer
     */
    public function getSuratJalansByCustomer(Request $request)
    {
        $kodeCustomer = $request->kode_customer;
        
        $suratJalans = SuratJalan::with(['customer', 'items'])
            ->where('kode_customer', $kodeCustomer)
            ->whereNull('no_transaksi') // Not yet converted to invoice
            ->orderBy('tanggal', 'desc')
            ->get();

        return response()->json($suratJalans);
    }

    /**
     * Store invoice from multiple delivery orders
     */
    public function storeMultipleFaktur(Request $request)
    {
        $request->validate([
            'kode_customer' => 'required|exists:customers,kode_customer',
            'surat_jalan_ids' => 'required|array|min:1',
            'surat_jalan_ids.*' => 'exists:surat_jalan,id',
            'tanggal' => 'required|date',
            'pembayaran' => 'required|string',
            'cara_bayar' => 'required|string',
            'sales' => 'required|exists:stok_owners,kode_stok_owner',
            'no_po' => 'required|string|max:50',
            'hari_tempo' => 'nullable|integer|min:0',
            'tanggal_jatuh_tempo' => 'nullable|date|after_or_equal:tanggal',
            'merge_similar_items' => 'nullable|in:true,false,1,0,on,off',
        ]);

        try {
            DB::beginTransaction();

            // Get customer info
            $customer = Customer::where('kode_customer', $request->kode_customer)->first();
            if (!$customer) {
                throw new \Exception('Customer not found');
            }

            // Get selected delivery orders
            $suratJalans = SuratJalan::with(['items.suratJalanItemSumber.stockBatch'])
                ->whereIn('id', $request->surat_jalan_ids)
                ->where('kode_customer', $request->kode_customer)
                ->get();

            if ($suratJalans->isEmpty()) {
                throw new \Exception('No valid delivery orders found');
            }

            // Generate invoice number
            $noTransaksi = Transaksi::generateNoTransaksi();

            // Use provided PO number (no auto-generate)
            $noPo = $request->no_po;

            // Auto compute dates
            $tanggalFaktur = Carbon::parse($request->tanggal);
            $tanggalJadi = $tanggalFaktur->copy();
            $tanggalJatuhTempo = null;
            $hariTempo = (int) ($request->hari_tempo ?? 0);
            if ($hariTempo > 0) {
                $tanggalJatuhTempo = $tanggalFaktur->copy()->addDays($hariTempo)->toDateString();
            }

            // Collect all items from selected delivery orders
            $allItems = collect();
            foreach ($suratJalans as $sj) {
                foreach ($sj->items as $item) {
                    $allItems->push([
                        'surat_jalan_id' => $sj->id,
                        'surat_jalan_item_id' => $item->id,
                        'kode_barang' => $item->kode_barang,
                        'nama_barang' => $item->nama_barang,
                        'qty' => $item->qty,
                        'satuan' => $item->satuan,
                        'satuan_besar' => $item->satuan_besar,
                        'sumber_data' => $item->suratJalanItemSumber
                    ]);
                }
            }

            // Merge similar items if requested
            if (filter_var($request->input('merge_similar_items'), FILTER_VALIDATE_BOOLEAN)) {
                $allItems = $this->mergeSimilarItems($allItems);
            }

            // Calculate totals
            $subtotal = 0;
            $grandTotal = 0;

            // Create invoice
            $transaksi = Transaksi::create([
                'no_transaksi' => $noTransaksi,
                'no_po' => $noPo,
                'tanggal' => $tanggalFaktur->toDateString(),
                'tanggal_jadi' => $tanggalJadi,
                'kode_customer' => $request->kode_customer,
                'sales' => $request->sales,
                'pembayaran' => $request->pembayaran,
                'cara_bayar' => $request->cara_bayar,
                'hari_tempo' => $request->hari_tempo ?? 0,
                'tanggal_jatuh_tempo' => $request->tanggal_jatuh_tempo ?: null,
                'subtotal' => $subtotal,
                'discount' => 0,
                'disc_rupiah' => 0,
                'ppn' => 0,
                'dp' => 0,
                'grand_total' => $grandTotal,
                'status' => 'pending',
                'status_piutang' => 'belum_dibayar',
                'total_dibayar' => 0,
                'sisa_piutang' => $grandTotal,
                'created_from_multiple_sj' => true,
                'notes' => 'Created from multiple delivery orders: ' . $suratJalans->pluck('no_suratjalan')->join(', ')
            ]);

            // Create invoice items and transfer FIFO data
            foreach ($allItems as $itemData) {
                // Get the first item to determine price (you might want to implement pricing logic)
                $kodeBarang = KodeBarang::where('kode_barang', $itemData['kode_barang'])->first();
                $harga = $kodeBarang ? $kodeBarang->cost * 1.2 : 0; // 20% markup as example
                $total = $itemData['qty'] * $harga;

                // Create transaction item
                $transaksiItem = TransaksiItem::create([
                    'transaksi_id' => $transaksi->id,
                    'no_transaksi' => $noTransaksi,
                    'kode_barang' => $itemData['kode_barang'],
                    'nama_barang' => $itemData['nama_barang'],
                    'harga' => $harga,
                    'qty' => $itemData['qty'],
                    'qty_sisa' => $itemData['qty'],
                    'satuan' => $itemData['satuan'],
                    'total' => $total,
                    'diskon' => 0,
                ]);

                // Transfer FIFO data from SuratJalanItemSumber to TransaksiItemSumber
                $this->transferFifoData($itemData, $transaksiItem->id);

                $subtotal += $total;
            }

            // Update totals
            $grandTotal = $subtotal; // Add PPN, discount logic here if needed
            $transaksi->update([
                'subtotal' => $subtotal,
                'grand_total' => $grandTotal,
                'sisa_piutang' => $grandTotal,
            ]);

            // Update delivery orders to link with invoice
            foreach ($suratJalans as $sj) {
                $sj->update([
                    'no_transaksi' => $noTransaksi,
                    'tanggal_transaksi' => $request->tanggal
                ]);
            }

            DB::commit();

            Log::info('Invoice created from multiple delivery orders', [
                'invoice_id' => $transaksi->id,
                'no_transaksi' => $noTransaksi,
                'surat_jalan_count' => $suratJalans->count(),
                'items_count' => $allItems->count(),
                'created_by' => auth()->user()->name ?? 'System'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Invoice berhasil dibuat dari ' . $suratJalans->count() . ' surat jalan',
                'transaksi_id' => $transaksi->id,
                'no_transaksi' => $noTransaksi,
                'redirect_url' => route('transaksi.show', $transaksi->id)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating invoice from multiple SJ', [
                'error' => $e->getMessage(),
                'request_data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Preview invoice before creation
     */
    public function previewMultipleFaktur(Request $request)
    {
        $request->validate([
            'kode_customer' => 'required|exists:customers,kode_customer',
            'surat_jalan_ids' => 'required|array|min:1',
            'surat_jalan_ids.*' => 'exists:surat_jalan,id',
            'merge_similar_items' => 'nullable|in:true,false,1,0,on,off',
        ]);

        $suratJalans = SuratJalan::with(['customer', 'items'])
            ->whereIn('id', $request->surat_jalan_ids)
            ->where('kode_customer', $request->kode_customer)
            ->get();

        // Collect and process items
        $allItems = collect();
        foreach ($suratJalans as $sj) {
            foreach ($sj->items as $item) {
                $allItems->push([
                    'surat_jalan_no' => $sj->no_suratjalan,
                    'surat_jalan_tanggal' => $sj->tanggal,
                    'kode_barang' => $item->kode_barang,
                    'nama_barang' => $item->nama_barang,
                    'qty' => $item->qty,
                    'satuan' => $item->satuan,
                    'satuan_besar' => $item->satuan_besar,
                ]);
            }
        }

        // Merge similar items if requested
        if (filter_var($request->input('merge_similar_items'), FILTER_VALIDATE_BOOLEAN)) {
            $allItems = $this->mergeSimilarItems($allItems);
        }

        return response()->json([
            'success' => true,
            'customer' => $suratJalans->first()->customer,
            'surat_jalans' => $suratJalans,
            'items' => $allItems,
            'total_items' => $allItems->count(),
            'total_qty' => $allItems->sum('qty')
        ]);
    }

    /**
     * Merge similar items (same kode_barang)
     */
    private function mergeSimilarItems($items)
    {
        $merged = $items->groupBy('kode_barang')->map(function ($group) {
            $first = $group->first();
            $totalQty = $group->sum('qty');
            $allSumber = $group->pluck('sumber_data')->flatten();

            return [
                'surat_jalan_id' => $group->pluck('surat_jalan_id')->unique()->values()->toArray(),
                'surat_jalan_item_id' => $group->pluck('surat_jalan_item_id')->toArray(),
                'kode_barang' => $first['kode_barang'],
                'nama_barang' => $first['nama_barang'],
                'qty' => $totalQty,
                'satuan' => $first['satuan'],
                'satuan_besar' => $first['satuan_besar'],
                'sumber_data' => $allSumber
            ];
        });

        return $merged->values();
    }

    /**
     * Transfer FIFO data from SuratJalanItemSumber to TransaksiItemSumber
     */
    private function transferFifoData($itemData, $transaksiItemId)
    {
        foreach ($itemData['sumber_data'] as $sumber) {
            TransaksiItemSumber::create([
                'transaksi_item_id' => $transaksiItemId,
                'stock_batch_id' => $sumber->stock_batch_id,
                'qty_diambil' => $sumber->qty_diambil,
                'harga_modal' => $sumber->harga_modal,
                'surat_jalan_item_sumber_id' => $sumber->id // Keep reference to original
            ]);
        }
    }
}
