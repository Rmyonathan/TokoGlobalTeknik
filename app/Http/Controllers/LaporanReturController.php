<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\TransaksiItem;
use App\Models\ReturPenjualan;
use App\Models\ReturPembelian;
use App\Models\StockBatch;
use App\Models\KodeBarang;

class LaporanReturController extends Controller
{
    /**
     * Show retur report page
     */
    public function index()
    {
        return view('laporan.retur_report');
    }

    /**
     * Get retur penjualan report
     */
    public function getReturPenjualanReport(Request $request)
    {
        $tanggalMulai = $request->get('tanggal_mulai', date('Y-m-01'));
        $tanggalSelesai = $request->get('tanggal_selesai', date('Y-m-d'));

        $data = TransaksiItem::select([
                'transaksi_items.no_transaksi',
                'transaksi_items.kode_barang',
                'transaksi_items.nama_barang',
                'transaksi_items.qty',
                'transaksi_items.qty_return',
                'transaksi_items.qty_sisa',
                'transaksi_items.harga',
                'transaksi_items.total',
                'customers.nama as customer_name',
                'transaksi.tanggal as tanggal_transaksi'
            ])
            ->join('transaksi', 'transaksi_items.no_transaksi', '=', 'transaksi.no_transaksi')
            ->leftJoin('customers', 'transaksi.kode_customer', '=', 'customers.kode_customer')
            ->where('transaksi_items.qty_return', '>', 0)
            ->whereBetween('transaksi.tanggal', [$tanggalMulai, $tanggalSelesai])
            ->orderBy('transaksi.tanggal', 'desc')
            ->get();

        return response()->json($data);
    }

    /**
     * Get retur pembelian report
     */
    public function getReturPembelianReport(Request $request)
    {
        $tanggalMulai = $request->get('tanggal_mulai', date('Y-m-01'));
        $tanggalSelesai = $request->get('tanggal_selesai', date('Y-m-d'));

        $data = DB::table('retur_pembelian_items')
            ->select([
                'retur_pembelian.no_pembelian',
                'retur_pembelian_items.kode_barang',
                'retur_pembelian_items.nama_barang',
                'pembelian_items.qty as qty_asli',
                'retur_pembelian_items.qty_retur',
                'retur_pembelian_items.harga',
                'retur_pembelian_items.total',
                'retur_pembelian.status',
                'suppliers.nama as supplier_name'
            ])
            ->join('retur_pembelian', 'retur_pembelian_items.retur_pembelian_id', '=', 'retur_pembelian.id')
            ->join('pembelian_items', 'retur_pembelian_items.pembelian_item_id', '=', 'pembelian_items.id')
            ->leftJoin('suppliers', 'retur_pembelian.kode_supplier', '=', 'suppliers.kode_supplier')
            ->whereBetween('retur_pembelian.tanggal', [$tanggalMulai, $tanggalSelesai])
            ->orderBy('retur_pembelian.tanggal', 'desc')
            ->get();

        return response()->json($data);
    }

    /**
     * Get dampak stok dari retur
     */
    public function getDampakStokRetur(Request $request)
    {
        $tanggalMulai = $request->get('tanggal_mulai', date('Y-m-01'));
        $tanggalSelesai = $request->get('tanggal_selesai', date('Y-m-d'));

        // Stok masuk dari retur penjualan
        $stokMasuk = DB::table('stock_batches')
            ->select([
                'kode_barangs.kode_barang',
                'kode_barangs.name as nama_barang',
                DB::raw('SUM(stock_batches.qty_masuk) as stok_masuk'),
                DB::raw('0 as stok_keluar'),
                DB::raw('MAX(stock_batches.tanggal_masuk) as tanggal')
            ])
            ->join('kode_barangs', 'stock_batches.kode_barang_id', '=', 'kode_barangs.id')
            ->where('stock_batches.tipe_batch', 'return_penjualan')
            ->whereBetween('stock_batches.tanggal_masuk', [$tanggalMulai, $tanggalSelesai])
            ->groupBy('kode_barangs.kode_barang', 'kode_barangs.name');

        // Stok keluar dari retur pembelian
        $stokKeluar = DB::table('stock_batches')
            ->select([
                'kode_barangs.kode_barang',
                'kode_barangs.name as nama_barang',
                DB::raw('0 as stok_masuk'),
                DB::raw('SUM(stock_batches.qty_masuk - stock_batches.qty_sisa) as stok_keluar'),
                DB::raw('MAX(stock_batches.tanggal_masuk) as tanggal')
            ])
            ->join('kode_barangs', 'stock_batches.kode_barang_id', '=', 'kode_barangs.id')
            ->where('stock_batches.keterangan', 'like', '%Retur Pembelian%')
            ->whereBetween('stock_batches.tanggal_masuk', [$tanggalMulai, $tanggalSelesai])
            ->groupBy('kode_barangs.kode_barang', 'kode_barangs.name');

        // Union kedua query
        $data = $stokMasuk->union($stokKeluar)->get();

        // Group by kode_barang dan hitung total
        $groupedData = $data->groupBy('kode_barang')->map(function ($items) {
            $firstItem = $items->first();
            $stokMasuk = $items->sum('stok_masuk');
            $stokKeluar = $items->sum('stok_keluar');
            $tanggal = $items->max('tanggal');

            // Get current stock
            $currentStock = StockBatch::join('kode_barangs', 'stock_batches.kode_barang_id', '=', 'kode_barangs.id')
                ->where('kode_barangs.kode_barang', $firstItem->kode_barang)
                ->sum('stock_batches.qty_sisa');

            return [
                'kode_barang' => $firstItem->kode_barang,
                'nama_barang' => $firstItem->nama_barang,
                'stok_masuk' => $stokMasuk,
                'stok_keluar' => $stokKeluar,
                'stok_saat_ini' => $currentStock,
                'tanggal' => $tanggal
            ];
        })->values();

        return response()->json($groupedData);
    }

    /**
     * Get summary retur
     */
    public function getSummaryRetur(Request $request)
    {
        $tanggalMulai = $request->get('tanggal_mulai', date('Y-m-01'));
        $tanggalSelesai = $request->get('tanggal_selesai', date('Y-m-d'));

        // Summary retur penjualan
        $returPenjualan = DB::table('retur_penjualan')
            ->whereBetween('tanggal', [$tanggalMulai, $tanggalSelesai])
            ->selectRaw('
                COUNT(*) as total_retur,
                SUM(total_retur) as total_nilai,
                COUNT(CASE WHEN status = "pending" THEN 1 END) as pending,
                COUNT(CASE WHEN status = "approved" THEN 1 END) as approved,
                COUNT(CASE WHEN status = "processed" THEN 1 END) as processed,
                COUNT(CASE WHEN status = "rejected" THEN 1 END) as rejected
            ')
            ->first();

        // Summary retur pembelian
        $returPembelian = DB::table('retur_pembelian')
            ->whereBetween('tanggal', [$tanggalMulai, $tanggalSelesai])
            ->selectRaw('
                COUNT(*) as total_retur,
                SUM(total_retur) as total_nilai,
                COUNT(CASE WHEN status = "pending" THEN 1 END) as pending,
                COUNT(CASE WHEN status = "approved" THEN 1 END) as approved,
                COUNT(CASE WHEN status = "processed" THEN 1 END) as processed,
                COUNT(CASE WHEN status = "rejected" THEN 1 END) as rejected
            ')
            ->first();

        return response()->json([
            'retur_penjualan' => $returPenjualan,
            'retur_pembelian' => $returPembelian
        ]);
    }
}
