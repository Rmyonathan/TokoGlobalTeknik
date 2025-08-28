<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Transaksi;
use App\Models\TransaksiItem;
use App\Models\TransaksiItemSumber;
use App\Models\StockBatch;
use App\Models\StokOwner;
use App\Models\KodeBarang;
use App\Models\Customer;
use Carbon\Carbon;

class LaporanController extends Controller
{
    /**
     * Display laporan index page
     */
    public function index()
    {
        return view('laporan.index');
    }

    /**
     * Laporan Laba per Faktur dengan logika FIFO baru
     */
    public function labaPerFaktur(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth());
        $endDate = $request->get('end_date', now()->endOfMonth());
        $customerId = $request->get('customer_id');
        
        try {
            // Base query for transactions
            $query = Transaksi::with(['customer', 'items.transaksiItemSumber.stockBatch'])
                ->whereBetween('tanggal', [$startDate, $endDate])
                ->where('status', '!=', 'canceled');

            if ($customerId) {
                $query->where('kode_customer', $customerId);
            }

            $transaksi = $query->orderBy('tanggal', 'desc')->get();

            $laporanData = [];
            $totalOmset = 0;
            $totalModal = 0;
            $totalLabaKotor = 0;
            $totalOngkosKuli = 0;
            $totalLabaBersih = 0;

            foreach ($transaksi as $t) {
                // Hitung modal berdasarkan FIFO dari transaksi_item_sumber
                $modalFaktur = 0;
                $ongkosKuliFaktur = 0;

                foreach ($t->items as $item) {
                    // Hitung ongkos kuli per item
                    $ongkosKuliFaktur += $item->ongkos_kuli ?? 0;

                    // Hitung modal dari sumber batch FIFO
                    foreach ($item->transaksiItemSumber as $sumber) {
                        if ($sumber->stockBatch) {
                            $modalFaktur += $sumber->qty_diambil * $sumber->stockBatch->harga_beli;
                        }
                    }
                }

                $omsetFaktur = $t->grand_total;
                $labaKotor = $omsetFaktur - $modalFaktur;
                $labaBersih = $labaKotor - $ongkosKuliFaktur;
                $marginKotor = $omsetFaktur > 0 ? ($labaKotor / $omsetFaktur) * 100 : 0;
                $marginBersih = $omsetFaktur > 0 ? ($labaBersih / $omsetFaktur) * 100 : 0;

                $laporanData[] = [
                    'no_transaksi' => $t->no_transaksi,
                    'tanggal' => $t->tanggal->format('d/m/Y'),
                    'customer' => $t->customer->nama ?? '-',
                    'omset' => $omsetFaktur,
                    'modal' => $modalFaktur,
                    'laba_kotor' => $labaKotor,
                    'ongkos_kuli' => $ongkosKuliFaktur,
                    'laba_bersih' => $labaBersih,
                    'margin_kotor' => $marginKotor,
                    'margin_bersih' => $marginBersih,
                    'status_piutang' => $t->status_piutang
                ];

                // Accumulate totals
                $totalOmset += $omsetFaktur;
                $totalModal += $modalFaktur;
                $totalLabaKotor += $labaKotor;
                $totalOngkosKuli += $ongkosKuliFaktur;
                $totalLabaBersih += $labaBersih;
            }

            $summary = [
                'total_faktur' => count($laporanData),
                'total_omset' => $totalOmset,
                'total_modal' => $totalModal,
                'total_laba_kotor' => $totalLabaKotor,
                'total_ongkos_kuli' => $totalOngkosKuli,
                'total_laba_bersih' => $totalLabaBersih,
                'margin_kotor_rata' => $totalOmset > 0 ? ($totalLabaKotor / $totalOmset) * 100 : 0,
                'margin_bersih_rata' => $totalOmset > 0 ? ($totalLabaBersih / $totalOmset) * 100 : 0,
                'roi' => $totalModal > 0 ? ($totalLabaBersih / $totalModal) * 100 : 0
            ];

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'data' => $laporanData,
                    'summary' => $summary
                ]);
            }

            return view('laporan.laba_per_faktur', compact('laporanData', 'summary', 'startDate', 'endDate'));

        } catch (\Exception $e) {
            Log::error('Error generating laporan laba per faktur:', ['message' => $e->getMessage()]);
            
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan: ' . $e->getMessage()
                ], 500);
            }

            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Laporan Laba per Barang dengan join ke transaksi_item_sumber dan stock_batches
     */
    public function labaPerBarang(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth());
        $endDate = $request->get('end_date', now()->endOfMonth());
        $kodeBarang = $request->get('kode_barang');

        try {
            // Query untuk mendapatkan data per barang dengan join yang kompleks
            $query = DB::table('transaksi_items as ti')
                ->join('transaksi as t', 'ti.transaksi_id', '=', 't.id')
                ->join('kode_barangs as kb', 'ti.kode_barang', '=', 'kb.kode_barang')
                ->leftJoin('customers as c', 't.kode_customer', '=', 'c.kode_customer')
                ->leftJoin('transaksi_item_sumber as tis', 'ti.id', '=', 'tis.transaksi_item_id')
                ->leftJoin('stock_batches as sb', 'tis.stock_batch_id', '=', 'sb.id')
                ->whereBetween('t.tanggal', [$startDate, $endDate])
                ->where('t.status', '!=', 'canceled')
                ->select([
                    'kb.kode_barang',
                    'kb.name as nama_barang',
                    'kb.attribute',
                    't.no_transaksi',
                    't.tanggal',
                    'c.nama as customer_nama',
                    'ti.qty',
                    'ti.satuan',
                    'ti.harga',
                    'ti.ongkos_kuli',
                    'tis.qty_diambil',
                    'sb.harga_beli',
                    'sb.tanggal_masuk'
                ]);

            if ($kodeBarang) {
                $query->where('kb.kode_barang', $kodeBarang);
            }

            $rawData = $query->orderBy('t.tanggal', 'desc')
                ->orderBy('kb.kode_barang')
                ->get();

            // Group dan hitung per barang
            $laporanData = [];
            $groupedData = $rawData->groupBy('kode_barang');

            foreach ($groupedData as $kodeBarang => $items) {
                $totalQty = 0;
                $totalOmset = 0;
                $totalModal = 0;
                $totalOngkosKuli = 0;
                $namaBarang = $items->first()->nama_barang;
                $attribute = $items->first()->attribute;

                // Detail per transaksi untuk barang ini
                $detailTransaksi = [];
                $transaksiGroup = $items->groupBy('no_transaksi');

                foreach ($transaksiGroup as $noTransaksi => $transaksiItems) {
                    $qtyTransaksi = $transaksiItems->sum('qty');
                    $omsetTransaksi = $transaksiItems->sum(function($item) {
                        return $item->qty * $item->harga;
                    });
                    $ongkosKuliTransaksi = $transaksiItems->sum('ongkos_kuli');
                    
                    // Hitung modal dari batch FIFO
                    $modalTransaksi = $transaksiItems->sum(function($item) {
                        return ($item->qty_diambil ?? 0) * ($item->harga_beli ?? 0);
                    });

                    $labaKotor = $omsetTransaksi - $modalTransaksi;
                    $labaBersih = $labaKotor - $ongkosKuliTransaksi;

                    $detailTransaksi[] = [
                        'no_transaksi' => $noTransaksi,
                        'tanggal' => $transaksiItems->first()->tanggal,
                        'customer' => $transaksiItems->first()->customer_nama ?? '-',
                        'qty' => $qtyTransaksi,
                        'satuan' => $transaksiItems->first()->satuan,
                        'omset' => $omsetTransaksi,
                        'modal' => $modalTransaksi,
                        'laba_kotor' => $labaKotor,
                        'ongkos_kuli' => $ongkosKuliTransaksi,
                        'laba_bersih' => $labaBersih,
                        'margin_kotor' => $omsetTransaksi > 0 ? ($labaKotor / $omsetTransaksi) * 100 : 0,
                        'margin_bersih' => $omsetTransaksi > 0 ? ($labaBersih / $omsetTransaksi) * 100 : 0
                    ];

                    $totalQty += $qtyTransaksi;
                    $totalOmset += $omsetTransaksi;
                    $totalModal += $modalTransaksi;
                    $totalOngkosKuli += $ongkosKuliTransaksi;
                }

                $totalLabaKotor = $totalOmset - $totalModal;
                $totalLabaBersih = $totalLabaKotor - $totalOngkosKuli;

                $laporanData[] = [
                    'kode_barang' => $kodeBarang,
                    'nama_barang' => $namaBarang,
                    'attribute' => $attribute,
                    'total_qty' => $totalQty,
                    'total_omset' => $totalOmset,
                    'total_modal' => $totalModal,
                    'total_laba_kotor' => $totalLabaKotor,
                    'total_ongkos_kuli' => $totalOngkosKuli,
                    'total_laba_bersih' => $totalLabaBersih,
                    'margin_kotor' => $totalOmset > 0 ? ($totalLabaKotor / $totalOmset) * 100 : 0,
                    'margin_bersih' => $totalOmset > 0 ? ($totalLabaBersih / $totalOmset) * 100 : 0,
                    'roi' => $totalModal > 0 ? ($totalLabaBersih / $totalModal) * 100 : 0,
                    'detail_transaksi' => $detailTransaksi
                ];
            }

            // Summary keseluruhan
            $grandSummary = [
                'total_jenis_barang' => count($laporanData),
                'total_transaksi' => $rawData->pluck('no_transaksi')->unique()->count(),
                'grand_total_qty' => collect($laporanData)->sum('total_qty'),
                'grand_total_omset' => collect($laporanData)->sum('total_omset'),
                'grand_total_modal' => collect($laporanData)->sum('total_modal'),
                'grand_total_laba_kotor' => collect($laporanData)->sum('total_laba_kotor'),
                'grand_total_ongkos_kuli' => collect($laporanData)->sum('total_ongkos_kuli'),
                'grand_total_laba_bersih' => collect($laporanData)->sum('total_laba_bersih'),
                'grand_margin_kotor' => collect($laporanData)->sum('total_omset') > 0 ? 
                    (collect($laporanData)->sum('total_laba_kotor') / collect($laporanData)->sum('total_omset')) * 100 : 0,
                'grand_margin_bersih' => collect($laporanData)->sum('total_omset') > 0 ? 
                    (collect($laporanData)->sum('total_laba_bersih') / collect($laporanData)->sum('total_omset')) * 100 : 0,
                'grand_roi' => collect($laporanData)->sum('total_modal') > 0 ? 
                    (collect($laporanData)->sum('total_laba_bersih') / collect($laporanData)->sum('total_modal')) * 100 : 0
            ];

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'data' => $laporanData,
                    'summary' => $grandSummary
                ]);
            }

            return view('laporan.laba_per_barang', compact('laporanData', 'grandSummary', 'startDate', 'endDate'));

        } catch (\Exception $e) {
            Log::error('Error generating laporan laba per barang:', ['message' => $e->getMessage()]);
            
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan: ' . $e->getMessage()
                ], 500);
            }

            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Rekapitulasi Ongkos Kuli
     */
    public function ongkosKuli(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth());
        $endDate = $request->get('end_date', now()->endOfMonth());
        $customerId = $request->get('customer_id');
        $kodeBarang = $request->get('kode_barang');

        try {
            $query = DB::table('transaksi_items as ti')
                ->join('transaksi as t', 'ti.transaksi_id', '=', 't.id')
                ->join('kode_barangs as kb', 'ti.kode_barang', '=', 'kb.id')
                ->leftJoin('customers as c', 't.kode_customer', '=', 'c.kode_customer')
                ->leftJoin('stok_owners as so', 't.sales', '=', 'so.kode_stok_owner')
                ->whereBetween('t.tanggal', [$startDate, $endDate])
                ->where('t.status', '!=', 'canceled')
                ->where('ti.ongkos_kuli', '>', 0);

            if ($customerId) {
                $query->where('t.kode_customer', $customerId);
            }

            if ($kodeBarang) {
                $query->where('kb.kode_barang', $kodeBarang);
            }

            $data = $query->select([
                't.no_transaksi',
                't.tanggal',
                'c.nama as customer_nama',
                'so.keterangan as sales_nama',
                'kb.kode_barang',
                'kb.name as nama_barang',
                'ti.qty',
                'ti.satuan',
                'ti.harga',
                'ti.ongkos_kuli',
                DB::raw('ti.qty * ti.harga as subtotal_item')
            ])
            ->orderBy('t.tanggal', 'desc')
            ->get();

            // Group by different criteria
            $groupByCustomer = $data->groupBy('customer_nama')->map(function($items, $customerNama) {
                return [
                    'customer_nama' => $customerNama ?: 'Unknown',
                    'total_ongkos_kuli' => $items->sum('ongkos_kuli'),
                    'total_omset' => $items->sum('subtotal_item'),
                    'jumlah_transaksi' => $items->pluck('no_transaksi')->unique()->count(),
                    'persentase_ongkos' => $items->sum('subtotal_item') > 0 ? 
                        ($items->sum('ongkos_kuli') / $items->sum('subtotal_item')) * 100 : 0
                ];
            })->values();

            $groupByBarang = $data->groupBy('kode_barang')->map(function($items, $kodeBarang) {
                return [
                    'kode_barang' => $kodeBarang,
                    'nama_barang' => $items->first()->nama_barang,
                    'total_qty' => $items->sum('qty'),
                    'total_ongkos_kuli' => $items->sum('ongkos_kuli'),
                    'total_omset' => $items->sum('subtotal_item'),
                    'rata_ongkos_per_unit' => $items->sum('qty') > 0 ? 
                        $items->sum('ongkos_kuli') / $items->sum('qty') : 0,
                    'persentase_ongkos' => $items->sum('subtotal_item') > 0 ? 
                        ($items->sum('ongkos_kuli') / $items->sum('subtotal_item')) * 100 : 0
                ];
            })->values();

            $summary = [
                'total_transaksi' => $data->pluck('no_transaksi')->unique()->count(),
                'total_items' => $data->count(),
                'total_ongkos_kuli' => $data->sum('ongkos_kuli'),
                'total_omset' => $data->sum('subtotal_item'),
                'rata_ongkos_per_transaksi' => $data->pluck('no_transaksi')->unique()->count() > 0 ? 
                    $data->sum('ongkos_kuli') / $data->pluck('no_transaksi')->unique()->count() : 0,
                'persentase_ongkos_terhadap_omset' => $data->sum('subtotal_item') > 0 ? 
                    ($data->sum('ongkos_kuli') / $data->sum('subtotal_item')) * 100 : 0
            ];

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'data' => $data,
                    'group_by_customer' => $groupByCustomer,
                    'group_by_barang' => $groupByBarang,
                    'summary' => $summary
                ]);
            }

            return view('laporan.ongkos_kuli', compact(
                'data', 'groupByCustomer', 'groupByBarang', 
                'summary', 'startDate', 'endDate'
            ));

        } catch (\Exception $e) {
            Log::error('Error generating laporan ongkos kuli:', ['message' => $e->getMessage()]);
            
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan: ' . $e->getMessage()
                ], 500);
            }

            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Laporan Komisi Sales dengan logika baru (0.4% dari total omset)
     */
    public function komisiSales(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth());
        $endDate = $request->get('end_date', now()->endOfMonth());
        $salesId = $request->get('sales_id');

        try {
            $query = Transaksi::with(['customer'])
                ->whereBetween('tanggal', [$startDate, $endDate])
                ->where('status', '!=', 'canceled')
                ->where('status_piutang', 'lunas') // ✅ hanya transaksi lunas
                ->whereNotNull('sales');

            if ($salesId) {
                $query->where('sales', $salesId);
            }

            $transaksi = $query->get();

            // Group by sales
            $laporanData = [];
            $groupedBySales = $transaksi->groupBy('sales');

            foreach ($groupedBySales as $salesCode => $salesTransaksi) {
                $salesman = StokOwner::where('kode_stok_owner', $salesCode)->first();
                $totalOmset = $salesTransaksi->sum('grand_total');
                $komisi = $totalOmset * 0.004; // 0.4%
                $jumlahFaktur = $salesTransaksi->count();
                $rataOmsetPerFaktur = $jumlahFaktur > 0 ? $totalOmset / $jumlahFaktur : 0;

                // Detail per customer
                $customerDetail = $salesTransaksi->groupBy('kode_customer')->map(function($customerTransaksi, $kodeCustomer) {
                    $customer = $customerTransaksi->first()->customer;
                    $omsetCustomer = $customerTransaksi->sum('grand_total');
                    
                    return [
                        'kode_customer' => $kodeCustomer,
                        'nama_customer' => $customer->nama ?? 'Unknown',
                        'jumlah_faktur' => $customerTransaksi->count(),
                        'total_omset' => $omsetCustomer
                    ];
                })->values();

                $laporanData[] = [
                    'sales_code' => $salesCode,
                    'sales_nama' => $salesman->keterangan ?? 'Unknown',
                    'jumlah_faktur' => $jumlahFaktur,
                    'total_omset' => $totalOmset,
                    'komisi' => $komisi,
                    'rata_omset_per_faktur' => $rataOmsetPerFaktur,
                    'komisi_per_faktur' => $jumlahFaktur > 0 ? $komisi / $jumlahFaktur : 0,
                    'customer_detail' => $customerDetail,
                    'periode_aktif' => [
                        'tanggal_pertama' => $salesTransaksi->min('tanggal')->format('d/m/Y'),
                        'tanggal_terakhir' => $salesTransaksi->max('tanggal')->format('d/m/Y')
                    ]
                ];
            }

            // Sort by total omset descending
            $laporanData = collect($laporanData)->sortByDesc('total_omset')->values()->all();

            $summary = [
                'total_sales' => count($laporanData),
                'total_faktur' => $transaksi->count(),
                'grand_total_omset' => $transaksi->sum('grand_total'),
                'grand_total_komisi' => $transaksi->sum('grand_total') * 0.004,
                'rata_omset_per_sales' => count($laporanData) > 0 ? 
                    $transaksi->sum('grand_total') / count($laporanData) : 0,
                'rata_komisi_per_sales' => count($laporanData) > 0 ? 
                    ($transaksi->sum('grand_total') * 0.004) / count($laporanData) : 0,
                'persentase_komisi' => 0.4, // Fixed 0.4%
                'sales_terbaik' => count($laporanData) > 0 ? $laporanData[0] : null
            ];

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'data' => $laporanData,
                    'summary' => $summary
                ]);
            }

            return view('laporan.komisi_sales', compact('laporanData', 'summary', 'startDate', 'endDate'));

        } catch (\Exception $e) {
            Log::error('Error generating laporan komisi sales:', ['message' => $e->getMessage()]);
            
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan: ' . $e->getMessage()
                ], 500);
            }

            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Laporan Stok dengan FIFO tracking
     */
    public function laporanStok(Request $request)
    {
        $kodeBarang = $request->get('kode_barang');
        $showBatches = (bool) $request->get('show_batches', false);

        try {
            $query = StockBatch::with(['kodeBarang'])
                ->where('qty_sisa', '>', 0);

            if ($kodeBarang) {
                $query->whereHas('kodeBarang', function($q) use ($kodeBarang) {
                    $q->where('kode_barang', $kodeBarang);
                });
            }

            $stockBatches = $query->orderBy('tanggal_masuk', 'asc')->get();

            if ($showBatches) {
                $laporanData = $stockBatches->map(function($batch) {
                    return [
                        'kode_barang' => $batch->kodeBarang->kode_barang,
                        'nama_barang' => $batch->kodeBarang->name,
                        'batch_id' => $batch->id,
                        'tanggal_masuk' => optional($batch->tanggal_masuk)->format('d/m/Y'),
                        'qty_masuk' => $batch->qty_masuk,
                        'qty_sisa' => $batch->qty_sisa,
                        'harga_beli' => $batch->harga_beli,
                        'total_nilai_sisa' => $batch->qty_sisa * $batch->harga_beli,
                    ];
                });
            } else {
                $groupedByBarang = $stockBatches->groupBy('kode_barang_id');
                $laporanData = $groupedByBarang->map(function($batches) {
                    $first = $batches->first();
                    $totalQtySisa = $batches->sum('qty_sisa');
                    $totalNilai = $batches->sum(function($b) { return $b->qty_sisa * $b->harga_beli; });
                    return [
                        'kode_barang' => $first->kodeBarang->kode_barang,
                        'nama_barang' => $first->kodeBarang->name,
                        'attribute' => $first->kodeBarang->attribute,
                        'total_qty_sisa' => $totalQtySisa,
                        'jumlah_batch' => $batches->count(),
                        'total_nilai_stok' => $totalNilai,
                        'rata_harga_beli' => $totalQtySisa > 0 ? $totalNilai / $totalQtySisa : 0,
                    ];
                })->values();
            }

            $summary = [
                'total_jenis_barang' => $showBatches ? $stockBatches->pluck('kode_barang_id')->unique()->count() : $laporanData->count(),
                'total_batch' => $stockBatches->count(),
                'grand_total_qty' => $stockBatches->sum('qty_sisa'),
                'grand_total_nilai' => $stockBatches->sum(function($b){ return $b->qty_sisa * $b->harga_beli; }),
            ];

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'data' => $laporanData,
                    'summary' => $summary,
                    'show_batches' => $showBatches
                ]);
            }

            return view('laporan.stok', compact('laporanData', 'summary', 'showBatches'));

        } catch (\Exception $e) {
            Log::error('Error generating laporan stok:', ['message' => $e->getMessage()]);
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Laporan Piutang Pelanggan
     */
    public function laporanPiutang(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth());
        $endDate = $request->get('end_date', now()->endOfMonth());
        $customerId = $request->get('customer_id');
        $statusPiutang = $request->get('status_piutang');
        $showJatuhTempo = (bool) $request->get('show_jatuh_tempo', false);

        try {
            $query = Transaksi::with(['customer', 'pembayaranDetails'])
                ->whereIn('status_piutang', ['belum_dibayar', 'sebagian'])
                ->whereBetween('tanggal', [$startDate, $endDate]);

            if ($customerId) {
                $query->where('kode_customer', $customerId);
            }

            if ($statusPiutang) {
                $query->where('status_piutang', $statusPiutang);
            }

            if ($showJatuhTempo) {
                $query->where('tanggal_jatuh_tempo', '<', now());
            }

            $custList = Customer::orderBy('nama')->get();

            $transaksi = $query->orderBy('tanggal_jatuh_tempo', 'asc')
                ->orderBy('tanggal', 'asc')
                ->get();
            // dd($transaksi);
            $laporanData = $transaksi->map(function($t) {
                $isOverdue = method_exists($t, 'checkJatuhTempo') ? $t->checkJatuhTempo() : false;
                $hariKeterlambatan = $isOverdue && $t->tanggal_jatuh_tempo ? now()->diffInDays($t->tanggal_jatuh_tempo) : 0;
                return [
                    'no_transaksi' => $t->no_transaksi,
                    'tanggal' => optional($t->tanggal)->format('d/m/Y'),
                    'tanggal_jatuh_tempo' => $t->tanggal_jatuh_tempo ? $t->tanggal_jatuh_tempo->format('d/m/Y') : '-',
                    'customer' => optional($t->customer)->nama ?? 'Unknown',
                    'kode_customer' => $t->kode_customer,
                    'total_faktur' => $t->grand_total,
                    'total_dibayar' => $t->total_dibayar,
                    'sisa_piutang' => $t->sisa_piutang,
                    'status_piutang' => $t->status_piutang,
                    'is_jatuh_tempo' => $isOverdue,
                    'hari_keterlambatan' => $hariKeterlambatan,
                    'kategori_keterlambatan' => $this->getKategoriKeterlambatan($hariKeterlambatan),
                    'persentase_pelunasan' => $t->grand_total > 0 ? ($t->total_dibayar / $t->grand_total) * 100 : 0
                ];
            });

            $groupByCustomer = $laporanData->groupBy('kode_customer')->map(function($rows, $kode) {
                $first = $rows->first();
                return [
                    'kode_customer' => $kode,
                    'nama_customer' => $first['customer'],
                    'jumlah_faktur' => $rows->count(),
                    'total_faktur' => $rows->sum('total_faktur'),
                    'total_dibayar' => $rows->sum('total_dibayar'),
                    'total_sisa_piutang' => $rows->sum('sisa_piutang'),
                    'faktur_jatuh_tempo' => $rows->where('is_jatuh_tempo', true)->count(),
                    'sisa_piutang_jatuh_tempo' => $rows->where('is_jatuh_tempo', true)->sum('sisa_piutang'),
                ];
            })->values();
            // dd($groupByCustomer);

            $summary = [
                'total_customer' => $laporanData->pluck('kode_customer')->unique()->count(),
                'total_faktur' => $laporanData->count(),
                'grand_total_faktur' => $laporanData->sum('total_faktur'),
                'grand_total_dibayar' => $laporanData->sum('total_dibayar'),
                'grand_sisa_piutang' => $laporanData->sum('sisa_piutang'),
                'faktur_jatuh_tempo' => $laporanData->where('is_jatuh_tempo', true)->count(),
                'sisa_piutang_jatuh_tempo' => $laporanData->where('is_jatuh_tempo', true)->sum('sisa_piutang'),
            ];

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'data' => $laporanData,
                    'group_by_customer' => $groupByCustomer,
                    'summary' => $summary
                ]);
            }
            // dd($transaksi);
            return view('laporan.piutang', compact('startDate', 'endDate', 'laporanData', 'groupByCustomer', 'summary', 'custList'));

        } catch (\Exception $e) {
            Log::error('Error generating laporan piutang:', ['message' => $e->getMessage()]);
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    private function getKategoriKeterlambatan($hari): string
    {
        if ($hari <= 0) return 'Normal';
        if ($hari <= 7) return 'Perhatian';
        if ($hari <= 30) return 'Bahaya';
        return 'Kritis';
    }
}