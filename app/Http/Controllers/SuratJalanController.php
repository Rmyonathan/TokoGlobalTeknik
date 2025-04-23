<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SuratJalan;
use App\Models\SuratJalanItem;
use App\Models\Customer;
use App\Models\Transaksi;
use App\Models\TransaksiItem;


class SuratJalanController extends Controller
{
    public function create(Request $request)
    {
        $lastSuratJalan = SuratJalan::orderBy('created_at', 'desc')->first();
        $newNumber = $lastSuratJalan ? ((int) substr($lastSuratJalan->no_suratjalan, -5)) + 1 : 1;
        $noSuratJalan = 'SJ-' . date('m') . date('y') . '-' . str_pad($newNumber, 5, '0', STR_PAD_LEFT);

        $customers = Customer::all();
        $noTransaksi = $request->get('no_transaksi'); // Jika ada no_transaksi dari query string
        $transaksi = $noTransaksi ? Transaksi::with('items')->where('no_transaksi', $noTransaksi)->first() : null;

        return view('suratjalan.suratjalan', compact('noSuratJalan', 'customers', 'transaksi'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'no_suratjalan' => 'required|unique:surat_jalan,no_suratjalan',
            'tanggal' => 'required|date',
            'kode_customer' => 'required|exists:customers,kode_customer',
            'alamat_suratjalan' => 'required|string',
            'no_transaksi' => 'nullable|exists:transaksi,no_transaksi',
            'items' => 'required|array',
            'items.*.transaksi_item_id' => 'required|exists:transaksi_items,id',
            'items.*.qty_dibawa' => 'required|numeric|min:1',
        ]);
    
        $suratJalan = SuratJalan::create([
            'no_suratjalan' => $request->no_suratjalan,
            'tanggal' => $request->tanggal,
            'kode_customer' => $request->kode_customer,
            'alamat_suratjalan' => $request->alamat_suratjalan,
            'no_transaksi' => $request->no_transaksi,
        ]);
    
        foreach ($request->items as $item) {
            $transaksiItem = TransaksiItem::findOrFail($item['transaksi_item_id']);
            $totalDibawa = SuratJalanItem::where('transaksi_item_id', $item['transaksi_item_id'])->sum('qty_dibawa');
    
            if ($item['qty_dibawa'] > ($transaksiItem->qty - $totalDibawa)) {
                return response()->json(['success' => false, 'message' => 'Qty Dibawa melebihi jumlah yang tersedia!'], 400);
            }
    
            SuratJalanItem::create([
                'surat_jalan_id' => $suratJalan->id,
                'transaksi_item_id' => $item['transaksi_item_id'],
                'qty_dibawa' => $item['qty_dibawa'],
            ]);
        }
    
        return response()->json(['success' => true, 'message' => 'Surat Jalan berhasil disimpan!', 'id' => $suratJalan->id]);
    }

    public function history()
    {
        $suratJalan = SuratJalan::with('customer', 'items.transaksiItem')->get();

        foreach ($suratJalan as $sj) {
            $totalQty = $sj->items->sum(function ($item) {
                return $item->transaksiItem->qty;
            });
            $totalDibawa = $sj->items->sum('qty_dibawa');

            $sj->status_barang = $totalDibawa >= $totalQty ? 'Selesai' : 'Belum Selesai';
        }

        return view('suratjalan.historysuratjalan', compact('suratJalan'));
    }

    public function detail($id)
    {
        $suratJalan = SuratJalan::with('items.transaksiItem', 'customer')->findOrFail($id);
        return view('suratjalan.detail', compact('suratJalan'));
    }
}