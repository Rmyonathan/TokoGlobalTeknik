<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SuratJalan;
use App\Models\SuratJalanItem;
use App\Models\SuratJalanItemSumber;
use App\Models\Customer;
use App\Models\Transaksi;
use App\Models\TransaksiItem;
use App\Models\StockBatch;
use App\Models\KodeBarang;
use App\Services\FifoService;
use App\Services\UnitConversionService;
use Illuminate\Support\Facades\DB;
use Exception;

use Illuminate\Support\Facades\Log;

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

        $customers = Customer::all();
        $noTransaksi = $request->get('no_transaksi'); // Jika ada no_transaksi dari query string
        $transaksi = $noTransaksi ? Transaksi::with('items')->where('no_transaksi', $noTransaksi)->first() : null;
        $transaksiItems = $transaksi ? $transaksi->items : collect();

        return view('suratjalan.suratjalan', compact('noSuratJalan', 'customers', 'transaksi', 'transaksiItems'));
    }

    public function store(Request $request){
        $request->validate([
            'no_suratjalan' => 'required|unique:surat_jalan,no_suratjalan',
            'tanggal' => 'required|date',
            'kode_customer' => 'required|exists:customers,kode_customer',
            'alamat_suratjalan' => 'required|string',
            'no_transaksi' => 'required|exists:transaksi,no_transaksi',
            'tanggal_transaksi' => 'required|date',
            'titipan_uang' => 'nullable|numeric',
            'sisa_piutang' => 'nullable|numeric',
            'items' => 'required|array',
            'items.*.transaksi_id' => 'required|integer',
            'items.*.no_transaksi' => 'required|exists:transaksi,no_transaksi',
            'items.*.kode_barang' => 'required|string',
            'items.*.nama_barang' => 'required|string',
            'items.*.qty' => 'required|numeric|min:0.01',
            // 'items.*.satuan' => 'required|string',
        ]);
    
        DB::beginTransaction();
        try {
            $suratJalan = SuratJalan::create([
                'no_suratjalan' => $request->no_suratjalan,
                'tanggal' => $request->tanggal ?? now(),
                'kode_customer' => $request->kode_customer,
                'alamat_suratjalan' => $request->alamat_suratjalan ?? "default",
                'no_transaksi' => $request->no_transaksi,
                'tanggal_transaksi' => $request->tanggal_transaksi,
                'titipan_uang' => $request->titipan_uang ?? 0,
                'sisa_piutang' => $request->sisa_piutang ?? 0,
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
                    $kodeBarang->unit_dasar
                    // $item['satuan']
                );

                // Validasi stok tersedia
                $stokTersedia = $this->fifoService->getStokTersedia($kodeBarang->id);
                if ($stokTersedia < $qtyInBaseUnit) {
                    throw new Exception("Stok tidak mencukupi untuk {$item['nama_barang']}. Tersedia: {$stokTersedia}, Dibutuhkan: {$qtyInBaseUnit}");
                }

                // Buat Surat Jalan Item
                $suratJalanItem = SuratJalanItem::create([
                    'no_suratjalan' => $suratJalan->no_suratjalan,
                    'transaksi_id' => $item['transaksi_id'],
                    'kode_barang' => $item['kode_barang'],
                    'nama_barang' => $item['nama_barang'],
                    'qty' => $item['qty']
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
            return response()->json([
                'error' => 'Terjadi kesalahan: ' . $e->getMessage()
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
        return view('suratjalan.detail', compact('suratJalan'));
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
}
