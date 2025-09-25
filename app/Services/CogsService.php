<?php

namespace App\Services;

use App\Models\Transaksi;
use App\Models\TransaksiItem;
use App\Models\TransaksiItemSumber;
use App\Models\SuratJalanItem;
use App\Models\SuratJalanItemSumber;
use App\Models\StockBatch;
use App\Models\PembelianItem;
use App\Models\KodeBarang;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CogsService
{
    /**
     * Hitung COGS untuk transaksi tertentu
     * 
     * @param int $transaksiId
     * @return array
     */
    public function calculateCogsForTransaction(int $transaksiId): array
    {
        $transaksi = Transaksi::with(['items.sumber'])->find($transaksiId);
        
        if (!$transaksi) {
            return [
                'success' => false,
                'message' => 'Transaksi tidak ditemukan'
            ];
        }

        $totalCogs = 0;
        $itemDetails = [];

        foreach ($transaksi->items as $item) {
            $itemCogs = $this->calculateItemCogs($item);
            $totalCogs += $itemCogs['total_cogs'];
            
            $itemDetails[] = [
                'kode_barang' => $item->kode_barang,
                'nama_barang' => $item->nama_barang,
                'qty' => $item->qty,
                'harga_jual' => $item->harga,
                'total_jual' => $item->total,
                'cogs_per_unit' => $itemCogs['cogs_per_unit'],
                'total_cogs' => $itemCogs['total_cogs'],
                'margin' => $item->total - $itemCogs['total_cogs'],
                'margin_percentage' => $item->total > 0 ? (($item->total - $itemCogs['total_cogs']) / $item->total) * 100 : 0,
                'batch_details' => $itemCogs['batch_details']
            ];
        }

        return [
            'success' => true,
            'transaksi_id' => $transaksiId,
            'no_transaksi' => $transaksi->no_transaksi,
            'tanggal' => $transaksi->tanggal,
            'total_penjualan' => $transaksi->grand_total,
            'total_cogs' => $totalCogs,
            'total_margin' => $transaksi->grand_total - $totalCogs,
            'margin_percentage' => $transaksi->grand_total > 0 ? (($transaksi->grand_total - $totalCogs) / $transaksi->grand_total) * 100 : 0,
            'item_details' => $itemDetails
        ];
    }

    /**
     * Hitung COGS untuk item transaksi tertentu
     * 
     * @param TransaksiItem $item
     * @return array
     */
    public function calculateItemCogs(TransaksiItem $item): array
    {
        $totalCogs = 0;
        $batchDetails = [];

        // Ambil semua sumber batch untuk item ini
        $sumber = $item->sumber;
        
        foreach ($sumber as $s) {
            $batchCogs = $s->qty_diambil * $s->harga_modal;
            $totalCogs += $batchCogs;
            
            $batchDetails[] = [
                'batch_id' => $s->stock_batch_id,
                'qty_diambil' => $s->qty_diambil,
                'harga_modal' => $s->harga_modal,
                'total_cogs' => $batchCogs,
                'tanggal_masuk' => $s->stockBatch->tanggal_masuk ?? null,
                'batch_number' => $s->stockBatch->batch_number ?? null
            ];
        }

        // Fallback: jika tidak ada sumber di TransaksiItemSumber (kasus faktur dari Surat Jalan),
        // estimasi COGS menggunakan data alokasi dari SuratJalanItemSumber.
        if ($totalCogs == 0) {
            // Ambil semua Surat Jalan yang ditautkan ke no_transaksi ini, lalu ambil itemnya untuk kode barang yang sama
            $sjItems = \App\Models\SuratJalan::with(['items.suratJalanItemSumber.stockBatch'])
                ->where('no_transaksi', $item->no_transaksi)
                ->get()
                ->flatMap(function ($sj) use ($item) {
                    return $sj->items->where('kode_barang', $item->kode_barang);
                });

            $totalQtyFromSj = 0;
            $totalCogsFromSj = 0;
            $aggregatedBatchDetails = [];

            foreach ($sjItems as $sjItem) {
                foreach ($sjItem->suratJalanItemSumber as $src) {
                    $batchCogs = (float) $src->qty_diambil * (float) $src->harga_modal;
                    $totalQtyFromSj += (float) $src->qty_diambil;
                    $totalCogsFromSj += $batchCogs;

                    $aggregatedBatchDetails[] = [
                        'batch_id' => $src->stock_batch_id,
                        'qty_diambil' => (float) $src->qty_diambil,
                        'harga_modal' => (float) $src->harga_modal,
                        'total_cogs' => $batchCogs,
                        'tanggal_masuk' => optional($src->stockBatch)->tanggal_masuk,
                        'batch_number' => optional($src->stockBatch)->batch_number,
                    ];
                }
            }

            if ($totalQtyFromSj > 0 && $totalCogsFromSj > 0) {
                // Alokasikan proporsional berdasarkan porsi qty item ini terhadap total qty untuk kode barang tsb di transaksi yang sama
                $totalQtySameKodePadaTransaksi = TransaksiItem::where('transaksi_id', $item->transaksi_id)
                    ->where('kode_barang', $item->kode_barang)
                    ->sum('qty');

                $proporsi = $totalQtySameKodePadaTransaksi > 0 ? ((float) $item->qty / (float) $totalQtySameKodePadaTransaksi) : 0;
                $totalCogs = $totalCogsFromSj * $proporsi;

                // Hitung cogs per unit berdasarkan rata-rata tertimbang dari data SJ
                $cogsPerUnitAvg = $totalCogsFromSj / $totalQtyFromSj;
                $batchDetails = [
                    [
                        'batch_id' => null,
                        'qty_diambil' => (float) $item->qty,
                        'harga_modal' => $cogsPerUnitAvg,
                        'total_cogs' => $cogsPerUnitAvg * (float) $item->qty,
                        'tanggal_masuk' => null,
                        'batch_number' => 'ESTIMATED-FROM-SJ'
                    ]
                ];
            }
        }

        return [
            'cogs_per_unit' => $item->qty > 0 ? $totalCogs / $item->qty : 0,
            'total_cogs' => $totalCogs,
            'batch_details' => $batchDetails
        ];
    }

    /**
     * Hitung COGS untuk periode tertentu
     * 
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @param string|null $kodeBarang
     * @return array
     */
    public function calculateCogsForPeriod(Carbon $startDate, Carbon $endDate, string $kodeBarang = null): array
    {
        $query = Transaksi::with(['items.sumber.stockBatch'])
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->where('status', '!=', 'canceled');

        if ($kodeBarang) {
            $query->whereHas('items', function($q) use ($kodeBarang) {
                $q->where('kode_barang', $kodeBarang);
            });
        }

        $transaksis = $query->get();

        $totalPenjualan = 0;
        $totalCogs = 0;
        $transaksiDetails = [];
        $barangSummary = [];

        foreach ($transaksis as $transaksi) {
            $transaksiCogs = $this->calculateCogsForTransaction($transaksi->id);
            
            if ($transaksiCogs['success']) {
                $totalPenjualan += $transaksiCogs['total_penjualan'];
                $totalCogs += $transaksiCogs['total_cogs'];
                
                $transaksiDetails[] = $transaksiCogs;

                // Summary per barang
                foreach ($transaksiCogs['item_details'] as $item) {
                    $kode = $item['kode_barang'];
                    if (!isset($barangSummary[$kode])) {
                        $barangSummary[$kode] = [
                            'kode_barang' => $kode,
                            'nama_barang' => $item['nama_barang'],
                            'total_qty' => 0,
                            'total_penjualan' => 0,
                            'total_cogs' => 0,
                            'total_margin' => 0
                        ];
                    }
                    
                    $barangSummary[$kode]['total_qty'] += $item['qty'];
                    $barangSummary[$kode]['total_penjualan'] += $item['total_jual'];
                    $barangSummary[$kode]['total_cogs'] += $item['total_cogs'];
                    $barangSummary[$kode]['total_margin'] += $item['margin'];
                }
            }
        }

        // Hitung margin percentage untuk summary barang
        foreach ($barangSummary as &$barang) {
            $barang['margin_percentage'] = $barang['total_penjualan'] > 0 
                ? ($barang['total_margin'] / $barang['total_penjualan']) * 100 
                : 0;
            $barang['cogs_per_unit'] = $barang['total_qty'] > 0 
                ? $barang['total_cogs'] / $barang['total_qty'] 
                : 0;
            $barang['margin_per_unit'] = $barang['total_qty'] > 0 
                ? max(0, ($barang['total_penjualan'] / $barang['total_qty']) - $barang['cogs_per_unit'])
                : 0;
        }

        return [
            'success' => true,
            'periode' => [
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d')
            ],
            'summary' => [
                'total_transaksi' => count($transaksiDetails),
                'total_penjualan' => $totalPenjualan,
                'total_cogs' => $totalCogs,
                'total_margin' => $totalPenjualan - $totalCogs,
                'margin_percentage' => $totalPenjualan > 0 ? (($totalPenjualan - $totalCogs) / $totalPenjualan) * 100 : 0
            ],
            'transaksi_details' => $transaksiDetails,
            'barang_summary' => array_values($barangSummary)
        ];
    }

    /**
     * Hitung rata-rata COGS untuk barang tertentu
     * 
     * @param string $kodeBarang
     * @param Carbon|null $startDate
     * @param Carbon|null $endDate
     * @return array
     */
    public function calculateAverageCogs(string $kodeBarang, Carbon $startDate = null, Carbon $endDate = null): array
    {
        $query = TransaksiItem::with(['sumber.stockBatch'])
            ->where('kode_barang', $kodeBarang)
            ->whereHas('transaksi', function($q) {
                $q->where('status', '!=', 'canceled');
            });

        if ($startDate && $endDate) {
            $query->whereHas('transaksi', function($q) use ($startDate, $endDate) {
                $q->whereBetween('tanggal', [$startDate, $endDate]);
            });
        }

        $items = $query->get();

        $totalQty = 0;
        $totalCogs = 0;
        $totalPenjualan = 0;
        $batchDetails = [];

        foreach ($items as $item) {
            $itemCogs = $this->calculateItemCogs($item);
            
            $totalQty += $item->qty;
            $totalCogs += $itemCogs['total_cogs'];
            $totalPenjualan += $item->total;
            
            foreach ($itemCogs['batch_details'] as $batch) {
                $batchDetails[] = $batch;
            }
        }

        return [
            'success' => true,
            'kode_barang' => $kodeBarang,
            'periode' => $startDate && $endDate ? [
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d')
            ] : null,
            'summary' => [
                'total_qty' => $totalQty,
                'total_penjualan' => $totalPenjualan,
                'total_cogs' => $totalCogs,
                'average_cogs_per_unit' => $totalQty > 0 ? $totalCogs / $totalQty : 0,
                'average_selling_price' => $totalQty > 0 ? $totalPenjualan / $totalQty : 0,
                'total_margin' => $totalPenjualan - $totalCogs,
                'margin_percentage' => $totalPenjualan > 0 ? (($totalPenjualan - $totalCogs) / $totalPenjualan) * 100 : 0
            ],
            'batch_details' => $batchDetails
        ];
    }

    /**
     * Hitung nilai persediaan saat ini berdasarkan FIFO
     * 
     * @param string|null $kodeBarang
     * @return array
     */
    public function calculateCurrentInventoryValue(string $kodeBarang = null): array
    {
        $query = StockBatch::with(['kodeBarang'])
            ->where('qty_sisa', '>', 0);

        if ($kodeBarang) {
            $query->whereHas('kodeBarang', function($q) use ($kodeBarang) {
                $q->where('kode_barang', $kodeBarang);
            });
        }

        $batches = $query->orderBy('tanggal_masuk', 'asc')->get();

        $totalValue = 0;
        $totalQty = 0;
        $barangDetails = [];

        foreach ($batches as $batch) {
            $batchValue = $batch->qty_sisa * $batch->harga_beli;
            $totalValue += $batchValue;
            $totalQty += $batch->qty_sisa;

            $kode = optional($batch->kodeBarang)->kode_barang;
            if (!$kode) { // skip batch without linked item
                $kode = $batch->kode_barang ?? 'UNKNOWN';
            }

            if (!isset($barangDetails[$kode])) {
                $kb = $batch->kodeBarang;
                $nama = $kb ? ($kb->name ?? $kb->nama_barang ?? '-') : '-';
                $barangDetails[$kode] = [
                    'kode_barang' => $kode,
                    'nama_barang' => $nama,
                    'total_qty' => 0,
                    'total_value' => 0,
                    'average_cost' => 0,
                    'batches' => []
                ];
            }

            $barangDetails[$kode]['total_qty'] += (float) $batch->qty_sisa;
            $barangDetails[$kode]['total_value'] += (float) $batchValue;
            $barangDetails[$kode]['average_cost'] = $barangDetails[$kode]['total_qty'] > 0
                ? $barangDetails[$kode]['total_value'] / $barangDetails[$kode]['total_qty']
                : 0;

            $barangDetails[$kode]['batches'][] = [
                'batch_id' => $batch->id,
                'qty_sisa' => $batch->qty_sisa,
                'harga_beli' => $batch->harga_beli,
                'tanggal_masuk' => $batch->tanggal_masuk,
                'batch_number' => $batch->batch_number
            ];
        }

        return [
            'success' => true,
            'summary' => [
                'total_qty' => $totalQty,
                'total_value' => $totalValue,
                'average_cost' => $totalQty > 0 ? $totalValue / $totalQty : 0
            ],
            'barang_details' => array_values($barangDetails)
        ];
    }

    /**
     * Generate COGS report data untuk chart
     * 
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return array
     */
    public function generateCogsChartData(Carbon $startDate, Carbon $endDate): array
    {
        $data = $this->calculateCogsForPeriod($startDate, $endDate);
        
        if (!$data['success']) {
            return $data;
        }

        // Group by date
        $dailyData = [];
        $currentDate = $startDate->copy();

        while ($currentDate->lte($endDate)) {
            $dateStr = $currentDate->format('Y-m-d');
            $dailyData[$dateStr] = [
                'tanggal' => $currentDate->format('d/m/Y'),
                'penjualan' => 0,
                'cogs' => 0,
                'margin' => 0,
                'transaksi_count' => 0
            ];
            $currentDate->addDay();
        }

        // Fill with actual data
        foreach ($data['transaksi_details'] as $transaksi) {
            $dateStr = Carbon::parse($transaksi['tanggal'])->format('Y-m-d');
            if (isset($dailyData[$dateStr])) {
                $dailyData[$dateStr]['penjualan'] += $transaksi['total_penjualan'];
                $dailyData[$dateStr]['cogs'] += $transaksi['total_cogs'];
                $dailyData[$dateStr]['margin'] += $transaksi['total_margin'];
                $dailyData[$dateStr]['transaksi_count']++;
            }
        }

        return [
            'success' => true,
            'chart_data' => array_values($dailyData),
            'summary' => $data['summary']
        ];
    }
}
