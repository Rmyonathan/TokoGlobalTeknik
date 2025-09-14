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
use App\Models\StockMutation;
use App\Models\StokOwner;
use App\Models\KodeBarang;
use App\Models\Customer;
use App\Models\ReturPenjualan;
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
     * Laporan Penjualan dan Retur Terpisah
     */
    public function penjualanDanRetur(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth());
        $endDate = $request->get('end_date', now()->endOfMonth());
        $customerId = $request->get('customer_id');
        
        try {
            // Query untuk data penjualan
            $penjualanQuery = Transaksi::with(['customer', 'items'])
                ->whereBetween('tanggal', [$startDate, $endDate])
                ->where('status', '!=', 'canceled');

            if ($customerId) {
                $penjualanQuery->where('kode_customer', $customerId);
            }

            $penjualanData = $penjualanQuery->orderBy('tanggal', 'desc')->get();

            // Query untuk data retur
            $returQuery = ReturPenjualan::with(['customer', 'transaksi', 'items'])
                ->whereBetween('tanggal', [$startDate, $endDate])
                ->whereIn('status', ['approved', 'processed']);

            if ($customerId) {
                $returQuery->where('kode_customer', $customerId);
            }

            $returData = $returQuery->orderBy('tanggal', 'desc')->get();

            // Hitung summary penjualan
            $penjualanSummary = [
                'total_faktur' => $penjualanData->count(),
                'total_omset' => $penjualanData->sum('grand_total'),
                'total_subtotal' => $penjualanData->sum('subtotal'),
                'total_diskon' => $penjualanData->sum('discount') + $penjualanData->sum('disc_rupiah'),
                'total_ppn' => $penjualanData->sum('ppn'),
                'rata_omset_per_faktur' => $penjualanData->count() > 0 ? $penjualanData->sum('grand_total') / $penjualanData->count() : 0,
            ];

            // Hitung summary retur
            $returSummary = [
                'total_retur' => $returData->count(),
                'total_nilai_retur' => $returData->sum('total_retur'),
                'rata_nilai_per_retur' => $returData->count() > 0 ? $returData->sum('total_retur') / $returData->count() : 0,
            ];

            // Hitung net sales (penjualan - retur)
            $netSales = $penjualanSummary['total_omset'] - $returSummary['total_nilai_retur'];

            // Group penjualan by customer
            $penjualanByCustomer = $penjualanData->groupBy('kode_customer')->map(function($transaksi, $kodeCustomer) {
                $customer = $transaksi->first()->customer;
                return [
                    'kode_customer' => $kodeCustomer,
                    'nama_customer' => $customer->nama ?? 'Unknown',
                    'jumlah_faktur' => $transaksi->count(),
                    'total_omset' => $transaksi->sum('grand_total'),
                    'transaksi_list' => $transaksi->map(function($t) {
                        return [
                            'no_transaksi' => $t->no_transaksi,
                            'tanggal' => $t->tanggal,
                            'grand_total' => $t->grand_total,
                            'status_piutang' => $t->status_piutang,
                        ];
                    })->values()
                ];
            })->values();

            // Group retur by customer
            $returByCustomer = $returData->groupBy('kode_customer')->map(function($retur, $kodeCustomer) {
                $customer = $retur->first()->customer;
                return [
                    'kode_customer' => $kodeCustomer,
                    'nama_customer' => $customer->nama ?? 'Unknown',
                    'jumlah_retur' => $retur->count(),
                    'total_nilai_retur' => $retur->sum('total_retur'),
                    'retur_list' => $retur->map(function($r) {
                        return [
                            'no_retur' => $r->no_retur,
                            'tanggal' => $r->tanggal,
                            'no_transaksi' => $r->no_transaksi,
                            'total_retur' => $r->total_retur,
                            'status' => $r->status,
                            'alasan_retur' => $r->alasan_retur,
                        ];
                    })->values()
                ];
            })->values();

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'penjualan_data' => $penjualanData,
                    'retur_data' => $returData,
                    'penjualan_summary' => $penjualanSummary,
                    'retur_summary' => $returSummary,
                    'net_sales' => $netSales,
                    'penjualan_by_customer' => $penjualanByCustomer,
                    'retur_by_customer' => $returByCustomer,
                ]);
            }

            return view('laporan.penjualan_dan_retur', compact(
                'penjualanData', 
                'returData', 
                'penjualanSummary', 
                'returSummary', 
                'netSales',
                'penjualanByCustomer',
                'returByCustomer',
                'startDate', 
                'endDate'
            ));

        } catch (\Exception $e) {
            Log::error('Error generating laporan penjualan dan retur:', ['message' => $e->getMessage()]);
            
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

                // Build item-level details for detail modal
                $itemDetails = $t->items->map(function($item){
                    return [
                        'kode_barang' => $item->kode_barang,
                        'nama_barang' => $item->nama_barang,
                        'qty' => (float) ($item->qty ?? 0),
                        'harga' => (float) ($item->harga ?? 0),
                        'sumber' => $item->transaksiItemSumber->map(function($s){
                            return [
                                'qty_diambil' => (float) ($s->qty_diambil ?? 0),
                                'harga_beli' => (float) optional($s->stockBatch)->harga_beli,
                            ];
                        })->values()
                    ];
                })->values();

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
                    'status_piutang' => $t->status_piutang,
                    'items' => $itemDetails,
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

                    // Detail item per faktur
                    $detailItems = $transaksiItems->map(function($item) {
                        return [
                            'kode_barang' => $item->kode_barang,
                            'nama_barang' => $item->nama_barang,
                            'qty' => $item->qty,
                            'satuan' => $item->satuan,
                            'harga' => $item->harga,
                            'subtotal' => $item->qty * $item->harga,
                            'ongkos_kuli' => $item->ongkos_kuli ?? 0,
                            'harga_beli' => $item->harga_beli ?? 0,
                            'qty_diambil' => $item->qty_diambil ?? 0,
                            'modal_item' => ($item->qty_diambil ?? 0) * ($item->harga_beli ?? 0)
                        ];
                    })->values();

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
                        'margin_bersih' => $omsetTransaksi > 0 ? ($labaBersih / $omsetTransaksi) * 100 : 0,
                        'detail_items' => $detailItems
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
                // Join ke master barang menggunakan kode_barang string, bukan ID
                ->join('kode_barangs as kb', 'ti.kode_barang', '=', 'kb.kode_barang')
                ->leftJoin('customers as c', 't.kode_customer', '=', 'c.kode_customer')
                ->leftJoin('wilayahs as w', 'c.wilayah_id', '=', 'w.id')
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
                    'transaksi_list' => $salesTransaksi->map(function($t){
                        return [
                            'no_transaksi' => $t->no_transaksi,
                            'tanggal' => optional($t->tanggal)->format('d/m/Y'),
                            'grand_total' => $t->grand_total,
                        ];
                    })->values(),
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
     * Detail faktur yang membentuk komisi untuk satu sales
     */
    public function komisiSalesDetail(Request $request, string $salesCode)
    {
        $startDate = $request->get('start_date', now()->startOfMonth());
        $endDate = $request->get('end_date', now()->endOfMonth());

        $salesman = StokOwner::where('kode_stok_owner', $salesCode)->first();

        $transaksi = Transaksi::with(['customer'])
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->where('status', '!=', 'canceled')
            ->where('status_piutang', 'lunas')
            ->where('sales', $salesCode)
            ->orderBy('tanggal', 'desc')
            ->get(['id','no_transaksi','tanggal','grand_total','kode_customer','sales']);

        return view('laporan.komisi_sales_detail', [
            'salesCode' => $salesCode,
            'salesNama' => $salesman->keterangan ?? 'Unknown',
            'startDate' => $startDate,
            'endDate' => $endDate,
            'transaksi' => $transaksi,
        ]);
    }

    /**
     * Detail item dalam satu faktur untuk laporan komisi sales
     */
    public function komisiSalesInvoiceDetail(Request $request, int $transaksiId)
    {
        try {
            $transaksi = Transaksi::with(['customer', 'items.kodeBarang'])
                ->where('id', $transaksiId)
                ->where('status', '!=', 'canceled')
                ->where('status_piutang', 'lunas')
                ->first();

            if (!$transaksi) {
                return response()->json([
                    'success' => false,
                    'message' => 'Transaksi tidak ditemukan'
                ], 404);
            }

            $items = $transaksi->items->map(function($item) {
                return [
                    'kode_barang' => $item->kode_barang,
                    'nama_barang' => $item->nama_barang,
                    'qty' => (float) ($item->qty ?? 0),
                    'satuan' => $item->satuan,
                    'harga' => (float) ($item->harga ?? 0),
                    'subtotal' => (float) ($item->qty ?? 0) * (float) ($item->harga ?? 0),
                    'ongkos_kuli' => (float) ($item->ongkos_kuli ?? 0)
                ];
            });

            $data = [
                'no_transaksi' => $transaksi->no_transaksi,
                'tanggal' => optional($transaksi->tanggal)->format('d/m/Y'),
                'customer' => optional($transaksi->customer)->nama ?? 'Unknown',
                'grand_total' => $transaksi->grand_total,
                'items' => $items,
                'total_items' => $items->count(),
                'total_qty' => $items->sum('qty'),
                'total_ongkos_kuli' => $items->sum('ongkos_kuli')
            ];

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'data' => $data
                ]);
            }

            return view('laporan.komisi_sales_invoice_detail', compact('data'));

        } catch (\Exception $e) {
            Log::error('Error getting invoice detail:', ['message' => $e->getMessage()]);
            
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
        // Get filter parameters
        $kodeBarang = $request->get('kode_barang');
        $namaBarang = $request->get('nama_barang');
        $grupBarangId = $request->get('grup_barang');
        $showBatches = (bool) $request->get('show_batches', false);
        $showPergerakan = (bool) $request->get('show_pergerakan', false);
        $tanggalPergerakan = $request->get('tanggal_pergerakan', now()->format('Y-m-d'));
        $jenisPergerakan = $request->get('jenis_pergerakan');

        try {
            // FIX: Use StockMutation instead of StockBatch for more accurate stock data
            $query = StockMutation::with(['kodeBarang'])
                ->where(function($q) {
                    $q->where('plus', '>', 0)
                      ->orWhere('minus', '>', 0);
                });

            // Apply filters using when() for dynamic query building
            $query->when($kodeBarang, function($q) use ($kodeBarang) {
                return $q->where('kode_barang', 'like', "%{$kodeBarang}%");
            });

            $query->when($namaBarang, function($q) use ($namaBarang) {
                return $q->where('nama_barang', 'like', "%{$namaBarang}%");
            });

            $query->when($grupBarangId, function($q) use ($grupBarangId) {
                return $q->whereHas('kodeBarang', function($subQ) use ($grupBarangId) {
                    $subQ->where('grup_barang_id', $grupBarangId);
                });
            });

            $stockMutations = $query->orderBy('tanggal', 'asc')->get();

            // Handle pergerakan barang jika diminta
            $laporanData = collect([]);
            $summary = [];
            $pergerakanData = collect([]);

            if ($showPergerakan) {
                // Query untuk stok masuk (pembelian, retur penjualan, dll)
                $stokMasukQuery = StockMutation::whereDate('tanggal', $tanggalPergerakan)
                    ->where('plus', '>', 0);

                // Query untuk stok keluar (penjualan, retur pembelian, dll)
                $stokKeluarQuery = StockMutation::whereDate('tanggal', $tanggalPergerakan)
                    ->where('minus', '>', 0);

                // Apply filters using when() for dynamic query building
                $applyFilters = function($query) use ($kodeBarang, $namaBarang, $grupBarangId) {
                    $query->when($kodeBarang, function($q) use ($kodeBarang) {
                        return $q->where('kode_barang', 'like', "%{$kodeBarang}%");
                    })
                    ->when($namaBarang, function($q) use ($namaBarang) {
                        return $q->where('nama_barang', 'like', "%{$namaBarang}%");
                    })
                    ->when($grupBarangId, function($q) use ($grupBarangId) {
                        // For StockMutation, we need to join with kode_barangs table to filter by grup_barang_id
                        return $q->join('kode_barangs', 'stock_mutations.kode_barang', '=', 'kode_barangs.kode_barang')
                            ->where('kode_barangs.grup_barang_id', $grupBarangId)
                            ->select('stock_mutations.*', 'stock_mutations.tanggal as tanggal');
                    });
                };

                $applyFilters($stokMasukQuery);
                $applyFilters($stokKeluarQuery);

                // Get data berdasarkan jenis pergerakan
                $stokMasuk = collect([]);
                $stokKeluar = collect([]);

                if (!$jenisPergerakan || $jenisPergerakan === 'semua' || $jenisPergerakan === 'masuk') {
                    $stokMasuk = $stokMasukQuery->orderBy('tanggal', 'asc')->get();
                }

                if (!$jenisPergerakan || $jenisPergerakan === 'semua' || $jenisPergerakan === 'keluar') {
                    $stokKeluar = $stokKeluarQuery->orderBy('tanggal', 'asc')->get();
                }

                // Format data untuk display
                foreach ($stokMasuk as $mutation) {
                    $pergerakanData->push([
                        'waktu' => optional($mutation->tanggal)->format('H:i:s') ?? '00:00:00',
                        'kode_barang' => $mutation->kode_barang,
                        'nama_barang' => $mutation->nama_barang,
                        'no_transaksi' => $mutation->no_transaksi,
                        'no_nota' => $mutation->no_nota,
                        'supplier_customer' => $mutation->supplier_customer,
                        'jenis_pergerakan' => 'MASUK',
                        'qty_masuk' => $mutation->plus,
                        'qty_keluar' => 0,
                        'satuan' => $mutation->satuan,
                        'keterangan' => $mutation->keterangan,
                        'created_by' => $mutation->created_by
                    ]);
                }

                foreach ($stokKeluar as $mutation) {
                    $pergerakanData->push([
                        'waktu' => optional($mutation->tanggal)->format('H:i:s') ?? '00:00:00',
                        'kode_barang' => $mutation->kode_barang,
                        'nama_barang' => $mutation->nama_barang,
                        'no_transaksi' => $mutation->no_transaksi,
                        'no_nota' => $mutation->no_nota,
                        'supplier_customer' => $mutation->supplier_customer,
                        'jenis_pergerakan' => 'KELUAR',
                        'qty_masuk' => 0,
                        'qty_keluar' => $mutation->minus,
                        'satuan' => $mutation->satuan,
                        'keterangan' => $mutation->keterangan,
                        'created_by' => $mutation->created_by
                    ]);
                }

                // Sort by waktu
                $pergerakanData = $pergerakanData->sortBy('waktu')->values();

                // Summary untuk pergerakan
                $summary = [
                    'tanggal' => $tanggalPergerakan,
                    'total_transaksi' => $pergerakanData->count(),
                    'total_masuk' => $stokMasuk->count(),
                    'total_keluar' => $stokKeluar->count(),
                    'total_qty_masuk' => $stokMasuk->sum('plus'),
                    'total_qty_keluar' => $stokKeluar->sum('minus'),
                    'selisih_qty' => $stokMasuk->sum('plus') - $stokKeluar->sum('minus'),
                    'jenis_barang_terlibat' => $pergerakanData->pluck('kode_barang')->unique()->count()
                ];
            } else {
                // Original stock report logic - FIX: Use StockMutation data
                if ($showBatches) {
                    $laporanData = $stockMutations->map(function($mutation) {
                        return [
                            'kode_barang' => $mutation->kode_barang,
                            'nama_barang' => $mutation->nama_barang,
                            'batch_id' => $mutation->id,
                            'tanggal_masuk' => optional($mutation->tanggal)->format('d/m/Y'),
                            'qty_masuk' => $mutation->plus,
                            'qty_sisa' => $mutation->plus - $mutation->minus, // Calculate remaining stock
                            'harga_beli' => 0, // StockMutation doesn't have harga_beli
                            'total_nilai_sisa' => 0, // Can't calculate without harga_beli
                            'action_pergerakan' => route('laporan.stok.pergerakan', $mutation->kode_barang),
                        ];
                    });
                } else {
                    $groupedByBarang = $stockMutations->groupBy('kode_barang');
                    $laporanData = $groupedByBarang->map(function($mutations) {
                        $first = $mutations->first();
                        $totalQtyMasuk = $mutations->sum('plus');
                        $totalQtyKeluar = $mutations->sum('minus');
                        $totalQtySisa = $totalQtyMasuk - $totalQtyKeluar;
                        return [
                            'kode_barang' => $first->kode_barang,
                            'nama_barang' => $first->nama_barang,
                            'attribute' => $first->kodeBarang ? $first->kodeBarang->attribute : 'N/A',
                            'total_qty_sisa' => $totalQtySisa,
                            'jumlah_batch' => $mutations->count(),
                            'total_nilai_stok' => 0, // Can't calculate without harga_beli
                            'rata_harga_beli' => 0, // Can't calculate without harga_beli
                            'action_pergerakan' => route('laporan.stok.pergerakan', $first->kode_barang),
                        ];
                    })->values();
                }
            }

            $summary = [
                'tanggal' => $tanggalPergerakan,
                'total_transaksi' => $stockMutations->count(),
                'total_masuk' => 0,
                'total_keluar' => 0,
                'total_qty_masuk' => 0,
                'total_qty_keluar' => 0,
                'selisih_qty' => 0,
                'jenis_barang_terlibat' => 0,
                'total_jenis_barang' => $showBatches ? $stockMutations->pluck('kode_barang')->unique()->count() : $laporanData->count(),
                'total_batch' => $stockMutations->count(),
                'grand_total_qty' => $stockMutations->sum('plus') - $stockMutations->sum('minus'),
                'grand_total_nilai' => 0, // Can't calculate without harga_beli
            ];

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'data' => $laporanData,
                    'summary' => $summary,
                    'show_batches' => $showBatches
                ]);
            }

            return view('laporan.stok', compact(
                'laporanData', 
                'summary', 
                'showBatches',
                'showPergerakan',
                'pergerakanData',
                'tanggalPergerakan',
                'jenisPergerakan',
                'kodeBarang',
                'namaBarang', 
                'grupBarangId'
            ));

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
        $statusKeterlambatan = $request->get('status_keterlambatan');
        $showJatuhTempo = (bool) $request->get('show_jatuh_tempo', false);

        try {
            $query = Transaksi::with(['customer', 'pembayaranDetails'])
                ->where('status', '!=', 'canceled')
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
                $today = now();
                $isOverdue = $t->tanggal_jatuh_tempo ? $today->gt($t->tanggal_jatuh_tempo) : false;
                $hariKeterlambatan = $isOverdue && $t->tanggal_jatuh_tempo ? $t->tanggal_jatuh_tempo->diffInDays($today) : 0;

                // Hitung total retur penjualan yang sudah approved untuk transaksi ini
                $totalReturApproved = \App\Models\ReturPenjualan::where('transaksi_id', $t->id)
                    ->whereIn('status', ['approved', 'processed'])
                    ->sum('total_retur');

                // Sisa piutang setelah dikurangi retur
                $sisaPiutangSetelahRetur = $t->sisa_piutang - $totalReturApproved;

                // Map status warna berdasarkan hari keterlambatan
                $statusWarna = null;
                $rowClass = null;
                
                // Jika faktur sudah lunas, beri warna hijau
                if ($sisaPiutangSetelahRetur <= 0) {
                    $statusWarna = 'hijau';
                    $rowClass = 'table-success'; // Hijau
                }
                // Jika faktur belum lunas dan ada keterlambatan, beri warna sesuai tingkat keterlambatan
                elseif ($sisaPiutangSetelahRetur > 0 && $hariKeterlambatan > 0) {
                    if ($hariKeterlambatan >= 1 && $hariKeterlambatan <= 15) {
                        $statusWarna = 'biru';
                        $rowClass = 'table-info'; // Biru muda
                    } elseif ($hariKeterlambatan >= 16 && $hariKeterlambatan <= 30) {
                        $statusWarna = 'kuning';
                        $rowClass = 'table-warning'; // Kuning
                    } elseif ($hariKeterlambatan > 30) {
                        $statusWarna = 'merah';
                        $rowClass = 'table-danger'; // Merah
                    }
                }
                // Jika faktur lunas tapi ada keterlambatan, tetap beri warna keterlambatan
                elseif ($sisaPiutangSetelahRetur <= 0 && $hariKeterlambatan > 0) {
                    if ($hariKeterlambatan >= 1 && $hariKeterlambatan <= 15) {
                        $statusWarna = 'biru';
                        $rowClass = 'table-info'; // Biru muda
                    } elseif ($hariKeterlambatan >= 16 && $hariKeterlambatan <= 30) {
                        $statusWarna = 'kuning';
                        $rowClass = 'table-warning'; // Kuning
                    } elseif ($hariKeterlambatan > 30) {
                        $statusWarna = 'merah';
                        $rowClass = 'table-danger'; // Merah
                    }
                }

                // Tentukan status piutang untuk tampilan berdasarkan aturan terbaru:
                // - lunas jika sisa setelah retur <= 0
                // - sebagian jika ada pembayaran (total_dibayar > 0)
                // - belum_dibayar jika belum ada pembayaran sama sekali
                $statusPiutangDisplay = $sisaPiutangSetelahRetur <= 0
                    ? 'lunas'
                    : ($t->total_dibayar > 0 ? 'sebagian' : 'belum_dibayar');

                return [
                    'no_transaksi' => $t->no_transaksi,
                    'tanggal' => optional($t->tanggal)->format('d/m/Y'),
                    'tanggal_jatuh_tempo' => $t->tanggal_jatuh_tempo ? $t->tanggal_jatuh_tempo->format('d/m/Y') : '-',
                    'customer' => optional($t->customer)->nama ?? 'Unknown',
                    'kode_customer' => $t->kode_customer,
                    'total_faktur' => $t->grand_total,
                    'total_dibayar' => $t->total_dibayar,
                    'total_retur' => $totalReturApproved,
                    'sisa_piutang' => $sisaPiutangSetelahRetur,
                    'sisa_piutang_original' => $t->sisa_piutang,
                    'status_piutang' => $statusPiutangDisplay,
                    'is_jatuh_tempo' => $isOverdue,
                    'hari_keterlambatan' => $hariKeterlambatan,
                    'status_warna' => $statusWarna,
                    'row_class' => $rowClass,
                    'status_keterlambatan' => $this->getStatusKeterlambatan($hariKeterlambatan),
                    'persentase_pelunasan' => $t->grand_total > 0 ? ($t->total_dibayar / $t->grand_total) * 100 : 0
                ];
            });

            // Filter berdasarkan status keterlambatan
            if ($statusKeterlambatan) {
                $laporanData = $laporanData->filter(function($row) use ($statusKeterlambatan) {
                    $hariKeterlambatan = $row['hari_keterlambatan'];
                    
                    switch ($statusKeterlambatan) {
                        case 'belum_jatuh_tempo':
                            return $hariKeterlambatan <= 0;
                        case '1-15':
                            return $hariKeterlambatan >= 1 && $hariKeterlambatan <= 15;
                        case '16-30':
                            return $hariKeterlambatan >= 16 && $hariKeterlambatan <= 30;
                        case '>30':
                            return $hariKeterlambatan > 30;
                        default:
                            return true;
                    }
                });
            }

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

    /**
     * Laporan Utang Supplier
     */
    public function laporanUtangSupplier(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth());
        $endDate = $request->get('end_date', now()->endOfMonth());
        $supplierId = $request->get('supplier_id');
        $statusUtang = $request->get('status_utang');
        $showJatuhTempo = (bool) $request->get('show_jatuh_tempo', false);

        try {
            $query = \App\Models\Pembelian::with(['supplier'])
                ->where('status', '!=', 'canceled')
                ->whereIn('status_utang', ['belum_dibayar', 'sebagian'])
                ->whereBetween('tanggal', [$startDate, $endDate]);

            if ($supplierId) {
                $query->where('kode_supplier', $supplierId);
            }

            if ($statusUtang) {
                $query->where('status_utang', $statusUtang);
            }

            if ($showJatuhTempo) {
                $query->where('tanggal_jatuh_tempo', '<', now());
            }

            $supplierList = \App\Models\Supplier::orderBy('nama')->get();

            $pembelian = $query->orderBy('tanggal_jatuh_tempo', 'asc')
                ->orderBy('tanggal', 'asc')
                ->get();

            $laporanData = $pembelian->map(function($p) {
                $today = now();
                $isOverdue = $p->tanggal_jatuh_tempo ? $today->gt($p->tanggal_jatuh_tempo) : false;
                $hariKeterlambatan = $isOverdue && $p->tanggal_jatuh_tempo ? $today->diffInDays($p->tanggal_jatuh_tempo) : 0;

                // Map status warna
                $statusWarna = null;
                if ($hariKeterlambatan >= 1 && $hariKeterlambatan <= 15) {
                    $statusWarna = 'biru';
                } elseif ($hariKeterlambatan >= 16 && $hariKeterlambatan <= 30) {
                    $statusWarna = 'kuning';
                } elseif ($hariKeterlambatan > 30) {
                    $statusWarna = 'merah';
                }

                // Bootstrap row class mapping
                $rowClass = null;
                if ($statusWarna === 'biru') {
                    $rowClass = 'table-info';
                } elseif ($statusWarna === 'kuning') {
                    $rowClass = 'table-warning';
                } elseif ($statusWarna === 'merah') {
                    $rowClass = 'table-danger';
                }

                return [
                    'nota' => $p->nota,
                    'tanggal' => optional($p->tanggal)->format('d/m/Y'),
                    'tanggal_jatuh_tempo' => $p->tanggal_jatuh_tempo ? $p->tanggal_jatuh_tempo->format('d/m/Y') : '-',
                    'supplier' => optional($p->supplier)->nama ?? 'Unknown',
                    'kode_supplier' => $p->kode_supplier,
                    'total_faktur' => $p->grand_total,
                    'total_dibayar' => $p->total_dibayar,
                    'sisa_utang' => $p->sisa_utang,
                    'status_utang' => $p->status_utang,
                    'is_jatuh_tempo' => $isOverdue,
                    'hari_keterlambatan' => $hariKeterlambatan,
                    'status_warna' => $statusWarna,
                    'row_class' => $rowClass,
                    'grup_keterlambatan' => $this->getGrupKeterlambatan($hariKeterlambatan),
                    'persentase_pelunasan' => $p->grand_total > 0 ? ($p->total_dibayar / $p->grand_total) * 100 : 0
                ];
            });

            $groupBySupplier = $laporanData->groupBy('kode_supplier')->map(function($rows, $kode) {
                $first = $rows->first();
                return [
                    'kode_supplier' => $kode,
                    'nama_supplier' => $first['supplier'],
                    'jumlah_faktur' => $rows->count(),
                    'total_faktur' => $rows->sum('total_faktur'),
                    'total_dibayar' => $rows->sum('total_dibayar'),
                    'total_sisa_utang' => $rows->sum('sisa_utang'),
                    'faktur_jatuh_tempo' => $rows->where('is_jatuh_tempo', true)->count(),
                    'sisa_utang_jatuh_tempo' => $rows->where('is_jatuh_tempo', true)->sum('sisa_utang'),
                ];
            })->values();

            $summary = [
                'total_supplier' => $laporanData->pluck('kode_supplier')->unique()->count(),
                'total_faktur' => $laporanData->count(),
                'grand_total_faktur' => $laporanData->sum('total_faktur'),
                'grand_total_dibayar' => $laporanData->sum('total_dibayar'),
                'grand_sisa_utang' => $laporanData->sum('sisa_utang'),
                'faktur_jatuh_tempo' => $laporanData->where('is_jatuh_tempo', true)->count(),
                'sisa_utang_jatuh_tempo' => $laporanData->where('is_jatuh_tempo', true)->sum('sisa_utang'),
            ];

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'data' => $laporanData,
                    'group_by_supplier' => $groupBySupplier,
                    'summary' => $summary
                ]);
            }

            return view('laporan.utang_supplier', compact('startDate', 'endDate', 'laporanData', 'groupBySupplier', 'summary', 'supplierList'));

        } catch (\Exception $e) {
            Log::error('Error generating laporan utang supplier:', ['message' => $e->getMessage()]);
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    private function getGrupKeterlambatan($hari): string
    {
        if ($hari <= 0) return 'Normal';
        if ($hari <= 7) return 'Perhatian';
        if ($hari <= 30) return 'Bahaya';
        return 'Kritis';
    }

    /**
     * Print laporan ongkos kuli
     */
    public function printOngkosKuli(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth());
        $endDate = $request->get('end_date', now()->endOfMonth());
        $customerId = $request->get('customer_id');
        $kodeBarang = $request->get('kode_barang');

        try {
            $query = DB::table('transaksi_items as ti')
                ->join('transaksi as t', 'ti.transaksi_id', '=', 't.id')
                ->join('kode_barangs as kb', 'ti.kode_barang', '=', 'kb.kode_barang')
                ->leftJoin('customers as c', 't.kode_customer', '=', 'c.kode_customer')
                ->leftJoin('wilayahs as w', 'c.wilayah_id', '=', 'w.id')
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
                'w.nama_wilayah as wilayah',
                'kb.kode_barang',
                'kb.name as nama_barang',
                'ti.qty',
                'ti.satuan',
                'ti.harga',
                'ti.ongkos_kuli',
                DB::raw('ti.qty * ti.harga as subtotal_item')
            ])
            ->orderBy('so.keterangan', 'asc')
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

            return view('laporan.print_ongkos_kuli', compact(
                'data', 'groupByCustomer', 'groupByBarang', 
                'summary', 'startDate', 'endDate'
            ));

        } catch (\Exception $e) {
            Log::error('Error generating print laporan ongkos kuli:', ['message' => $e->getMessage()]);
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Print laporan laba per faktur
     */
    public function printLabaPerFaktur(Request $request)
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
                    'status_piutang' => $t->status_piutang,
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

            return view('laporan.print_laba_per_faktur', compact('laporanData', 'summary', 'startDate', 'endDate'));

        } catch (\Exception $e) {
            Log::error('Error generating print laporan laba per faktur:', ['message' => $e->getMessage()]);
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Detail pergerakan barang per kode barang
     */
    public function detailPergerakanBarang(Request $request, string $kodeBarang)
    {
        $startDate = $request->get('start_date', now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));
        $jenisPergerakan = $request->get('jenis_pergerakan');

        try {
            // Get barang info
            $barang = KodeBarang::where('kode_barang', $kodeBarang)->first();
            
            if (!$barang) {
                return back()->with('error', 'Barang tidak ditemukan');
            }

            // Query untuk stok masuk
            $stokMasukQuery = StockMutation::where('kode_barang', $kodeBarang)
                ->whereBetween('tanggal', [$startDate, $endDate])
                ->where('plus', '>', 0);

            // Query untuk stok keluar
            $stokKeluarQuery = StockMutation::where('kode_barang', $kodeBarang)
                ->whereBetween('tanggal', [$startDate, $endDate])
                ->where('minus', '>', 0);

            // Get data berdasarkan jenis pergerakan
            $stokMasuk = collect([]);
            $stokKeluar = collect([]);

            if (!$jenisPergerakan || $jenisPergerakan === 'semua' || $jenisPergerakan === 'masuk') {
                $stokMasuk = $stokMasukQuery->orderBy('tanggal', 'desc')->get();
            }

            if (!$jenisPergerakan || $jenisPergerakan === 'semua' || $jenisPergerakan === 'keluar') {
                $stokKeluar = $stokKeluarQuery->orderBy('tanggal', 'desc')->get();
            }

            // Jika tidak ada data mutasi di tabel stock_mutations, fallback dari sumber lain
            if ($stokMasuk->isEmpty() && $stokKeluar->isEmpty()) {
                try {
                    // Fallback MASUK dari stock_batches
                    $batches = \App\Models\StockBatch::where('kode_barang_id', $barang->id)
                        ->whereBetween(\DB::raw('DATE(tanggal_masuk)'), [$startDate, $endDate])
                        ->orderBy('tanggal_masuk', 'desc')
                        ->get();
                    foreach ($batches as $b) {
                        $stokMasuk->push(new \App\Models\StockMutation([
                            'kode_barang' => $kodeBarang,
                            'nama_barang' => $barang->name,
                            'no_transaksi' => $b->batch_number ?? '-',
                            'tanggal' => \Carbon\Carbon::parse($b->tanggal_masuk),
                            'no_nota' => $b->batch_number ?? '-',
                            'supplier_customer' => '-',
                            'plus' => $b->qty_masuk,
                            'minus' => 0,
                            'total' => 0,
                            'so' => 'default',
                            'satuan' => $barang->unit_dasar ?? 'LBR',
                            'keterangan' => 'Fallback dari batch',
                            'created_by' => 'SYSTEM'
                        ]));
                    }

                    // Fallback KELUAR dari transaksi_items + transaksi
                    $txItems = \App\Models\TransaksiItem::where('kode_barang', $kodeBarang)
                        ->whereHas('transaksi', function($q) use ($startDate, $endDate) {
                            $q->whereBetween('tanggal', [$startDate, $endDate])
                              ->where('status', '!=', 'canceled');
                        })
                        ->with('transaksi')
                        ->orderByDesc('id')
                        ->get();
                    foreach ($txItems as $ti) {
                        $stokKeluar->push(new \App\Models\StockMutation([
                            'kode_barang' => $kodeBarang,
                            'nama_barang' => $ti->nama_barang,
                            'no_transaksi' => $ti->transaksi->no_transaksi ?? '-',
                            'tanggal' => \Carbon\Carbon::parse($ti->transaksi->tanggal ?? now()),
                            'no_nota' => $ti->transaksi->no_transaksi ?? '-',
                            'supplier_customer' => $ti->transaksi->customer->nama ?? '-',
                            'plus' => 0,
                            'minus' => $ti->qty,
                            'total' => 0,
                            'so' => $ti->transaksi->sales ?? 'default',
                            'satuan' => $ti->satuan ?? ($barang->unit_dasar ?? 'LBR'),
                            'keterangan' => 'Fallback dari transaksi',
                            'created_by' => $ti->transaksi->edited_by ?? 'SYSTEM'
                        ]));
                    }
                } catch (\Exception $e) {
                    // ignore fallback errors
                }
            }

            // Format data untuk display (tambahkan informasi harga beli/jual bila tersedia)
            $pergerakanData = collect([]);

            foreach ($stokMasuk as $mutation) {
                // Coba temukan harga beli terkait (pakai batch terakhir sebelum/di tanggal tersebut)
                $hargaBeli = null;
                try {
                    $batch = \App\Models\StockBatch::where('kode_barang_id', $barang->id)
                        ->whereDate('tanggal_masuk', '<=', $mutation->tanggal->format('Y-m-d'))
                        ->orderBy('tanggal_masuk', 'desc')
                        ->first();
                    if ($batch) {
                        $hargaBeli = $batch->harga_beli;
                    }
                } catch (\Exception $e) {
                    // ignore
                }

                $pergerakanData->push([
                    'tanggal' => $mutation->tanggal->format('d/m/Y'),
                    'waktu' => $mutation->tanggal->format('H:i:s'),
                    'no_transaksi' => $mutation->no_transaksi,
                    'no_nota' => $mutation->no_nota,
                    'supplier_customer' => $mutation->supplier_customer,
                    'jenis_pergerakan' => 'MASUK',
                    'qty_masuk' => $mutation->plus,
                    'qty_keluar' => 0,
                    'satuan' => $mutation->satuan,
                    'keterangan' => $mutation->keterangan,
                    'created_by' => $mutation->created_by,
                    'harga' => $hargaBeli,
                    'tipe_harga' => $hargaBeli !== null ? 'Beli' : null
                ]);
            }

            foreach ($stokKeluar as $mutation) {
                // Coba temukan harga jual terkait dari transaksi item
                $hargaJual = null;
                try {
                    $transaksi = \App\Models\Transaksi::where('no_transaksi', $mutation->no_nota)->first();
                    if ($transaksi) {
                        $item = \App\Models\TransaksiItem::where('transaksi_id', $transaksi->id)
                            ->where('kode_barang', $kodeBarang)
                            ->first();
                        if ($item) {
                            $hargaJual = $item->harga;
                        }
                    }
                } catch (\Exception $e) {
                    // ignore
                }

                $pergerakanData->push([
                    'tanggal' => $mutation->tanggal->format('d/m/Y'),
                    'waktu' => $mutation->tanggal->format('H:i:s'),
                    'no_transaksi' => $mutation->no_transaksi,
                    'no_nota' => $mutation->no_nota,
                    'supplier_customer' => $mutation->supplier_customer,
                    'jenis_pergerakan' => 'KELUAR',
                    'qty_masuk' => 0,
                    'qty_keluar' => $mutation->minus,
                    'satuan' => $mutation->satuan,
                    'keterangan' => $mutation->keterangan,
                    'created_by' => $mutation->created_by,
                    'harga' => $hargaJual,
                    'tipe_harga' => $hargaJual !== null ? 'Jual' : null
                ]);
            }

            // Sort by tanggal desc, then waktu desc
            $pergerakanData = $pergerakanData->sortByDesc('tanggal')->sortByDesc('waktu')->values();

            // Summary
            $summary = [
                'kode_barang' => $kodeBarang,
                'nama_barang' => $barang->name,
                'attribute' => $barang->attribute,
                'periode' => [
                    'start_date' => $startDate,
                    'end_date' => $endDate
                ],
                'total_transaksi' => $pergerakanData->count(),
                'total_masuk' => $stokMasuk->count(),
                'total_keluar' => $stokKeluar->count(),
                'total_qty_masuk' => $stokMasuk->sum('plus'),
                'total_qty_keluar' => $stokKeluar->sum('minus'),
                'selisih_qty' => $stokMasuk->sum('plus') - $stokKeluar->sum('minus'),
            ];

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'data' => $pergerakanData,
                    'summary' => $summary
                ]);
            }

            return view('laporan.detail_pergerakan_barang', compact(
                'pergerakanData', 
                'summary', 
                'startDate', 
                'endDate',
                'jenisPergerakan'
            ));

        } catch (\Exception $e) {
            Log::error('Error getting detail pergerakan barang:', ['message' => $e->getMessage()]);
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Get status keterlambatan berdasarkan hari
     */
    private function getStatusKeterlambatan($hari)
    {
        if ($hari <= 0) return 'Tepat Waktu';
        if ($hari >= 1 && $hari <= 15) return 'Perhatian (1-15 hari)';
        if ($hari >= 16 && $hari <= 30) return 'Bahaya (16-30 hari)';
        return 'Kritis (>30 hari)';
    }
}