<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\CogsService;
use App\Models\KodeBarang;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class CogsController extends Controller
{
    protected $cogsService;

    public function __construct(CogsService $cogsService)
    {
        $this->cogsService = $cogsService;
    }

    /**
     * Tampilkan halaman laporan COGS
     */
    public function index()
    {
        return view('laporan.cogs.index');
    }

    /**
     * Laporan COGS per periode
     */
    public function report(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'kode_barang' => 'nullable|string'
        ]);

        try {
            $startDate = Carbon::parse($request->start_date);
            $endDate = Carbon::parse($request->end_date);
            $kodeBarang = $request->kode_barang;

            $data = $this->cogsService->calculateCogsForPeriod($startDate, $endDate, $kodeBarang);

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json($data);
            }

            return view('laporan.cogs.report', compact('data', 'startDate', 'endDate', 'kodeBarang'));
        } catch (\Exception $e) {
            Log::error('COGS Report Error: ' . $e->getMessage());
            
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan saat menghitung laporan COGS: ' . $e->getMessage()
                ], 500);
            }

            return back()->with('error', 'Terjadi kesalahan saat menghitung laporan COGS: ' . $e->getMessage());
        }
    }

    /**
     * Laporan COGS per transaksi
     */
    public function transactionReport(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date'
        ]);

        try {
            $startDate = Carbon::parse($request->start_date);
            $endDate = Carbon::parse($request->end_date);

            $data = $this->cogsService->calculateCogsForPeriod($startDate, $endDate);

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json($data);
            }

            return view('laporan.cogs.transaction', compact('data', 'startDate', 'endDate'));
        } catch (\Exception $e) {
            Log::error('COGS Transaction Report Error: ' . $e->getMessage());
            
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan saat menghitung laporan COGS transaksi: ' . $e->getMessage()
                ], 500);
            }

            return back()->with('error', 'Terjadi kesalahan saat menghitung laporan COGS transaksi: ' . $e->getMessage());
        }
    }

    /**
     * Laporan COGS per barang
     */
    public function productReport(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'kode_barang' => 'required|string'
        ]);

        try {
            $startDate = Carbon::parse($request->start_date);
            $endDate = Carbon::parse($request->end_date);
            $kodeBarang = $request->kode_barang;

            $data = $this->cogsService->calculateAverageCogs($kodeBarang, $startDate, $endDate);

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json($data);
            }

            return view('laporan.cogs.product', compact('data', 'startDate', 'endDate', 'kodeBarang'));
        } catch (\Exception $e) {
            Log::error('COGS Product Report Error: ' . $e->getMessage());
            
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan saat menghitung laporan COGS barang: ' . $e->getMessage()
                ], 500);
            }

            return back()->with('error', 'Terjadi kesalahan saat menghitung laporan COGS barang: ' . $e->getMessage());
        }
    }

    /**
     * Laporan nilai persediaan saat ini
     */
    public function inventoryValue(Request $request)
    {
        $request->validate([
            'kode_barang' => 'nullable|string'
        ]);

        try {
            $kodeBarang = $request->kode_barang;
            $data = $this->cogsService->calculateCurrentInventoryValue($kodeBarang);

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json($data);
            }

            return view('laporan.cogs.inventory', compact('data', 'kodeBarang'));
        } catch (\Exception $e) {
            Log::error('COGS Inventory Value Error: ' . $e->getMessage());
            
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan saat menghitung nilai persediaan: ' . $e->getMessage()
                ], 500);
            }

            return back()->with('error', 'Terjadi kesalahan saat menghitung nilai persediaan: ' . $e->getMessage());
        }
    }

    /**
     * Inventory value detail per item (open in new blade instead of modal)
     */
    public function inventoryItem(string $kodeBarang)
    {
        try {
            $data = $this->cogsService->calculateCurrentInventoryValue($kodeBarang);
            if (!$data['success']) {
                return back()->with('error', $data['message'] ?? 'Gagal mengambil data persediaan');
            }
            // Ambil satu barang saja
            $barang = collect($data['barang_details'] ?? [])->first();
            if (!$barang) {
                $barang = [
                    'kode_barang' => $kodeBarang,
                    'nama_barang' => '-',
                    'total_qty' => 0,
                    'total_value' => 0,
                    'average_cost' => 0,
                    'batches' => []
                ];
            }
            return view('laporan.cogs.inventory_item', compact('barang'));
        } catch (\Exception $e) {
            \Log::error('COGS Inventory Item Error: '.$e->getMessage());
            return back()->with('error', 'Terjadi kesalahan saat membuka detail persediaan barang.');
        }
    }

    /**
     * Chart data untuk COGS
     */
    public function chartData(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date'
        ]);

        try {
            $startDate = Carbon::parse($request->start_date);
            $endDate = Carbon::parse($request->end_date);

            $data = $this->cogsService->generateCogsChartData($startDate, $endDate);

            return response()->json($data);
        } catch (\Exception $e) {
            Log::error('COGS Chart Data Error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data chart: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Detail COGS untuk transaksi tertentu
     */
    public function transactionDetail($transaksiId)
    {
        try {
            $data = $this->cogsService->calculateCogsForTransaction($transaksiId);

            if (request()->wantsJson() || request()->ajax()) {
                return response()->json($data);
            }

            return view('laporan.cogs.detail', compact('data'));
        } catch (\Exception $e) {
            Log::error('COGS Transaction Detail Error: ' . $e->getMessage());
            
            if (request()->wantsJson() || request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan saat mengambil detail transaksi: ' . $e->getMessage()
                ], 500);
            }

            return back()->with('error', 'Terjadi kesalahan saat mengambil detail transaksi: ' . $e->getMessage());
        }
    }

    /**
     * Get list barang untuk dropdown
     */
    public function getProducts(Request $request)
    {
        try {
            $search = $request->get('search', '');
            
            $products = KodeBarang::where('name', 'like', "%{$search}%")
                ->orWhere('kode_barang', 'like', "%{$search}%")
                ->select('kode_barang', 'name as nama_barang')
                ->limit(20)
                ->get();

            return response()->json([
                'success' => true,
                'data' => $products
            ]);
        } catch (\Exception $e) {
            Log::error('Get Products Error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data barang: ' . $e->getMessage()
            ], 500);
        }
    }
}
