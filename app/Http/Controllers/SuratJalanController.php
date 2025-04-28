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
            'items.*.no_transaksi' => 'required|exists:transaksi,no_transaksi',
        ]);
    
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
            SuratJalanItem::create([
                'no_suratjalan' => $suratJalan->no_suratjalan,
                'transaksi_id' => $item['transaksi_id'],
                'kode_barang' => $item['kode_barang'],
                'nama_barang' => $item['nama_barang'],
                'qty' => $item['qty']
            ]);
        }
    
        return response()->json([
            'message' => 'Surat Jalan berhasil disimpan!', 
            'id' => $suratJalan->id,
            'no_suratjalan'=> $suratJalan->no_suratjalan,
            'no_transaksi' => $suratJalan->no_transaksi,
            'tanggal' => $suratJalan->tanggal,
            'kode_customer' => $suratJalan->kode_customer,
            'alamat_suratjalan' => $suratJalan->alamat_suratjalan,
            'grand_total' => $request->grand_total ?? 0
        ]);

    }

    public function history()
    {
        $suratJalan = SuratJalan::with('customer', 'items.transaksiItem')->get();
        return view('suratjalan.historysuratjalan', compact('suratJalan'));
    }

    public function detail($id)
    {
        $suratJalan = SuratJalan::with('items.transaksiItem', 'customer')->findOrFail($id);
        return view('suratjalan.detail', compact('suratJalan'));
    }
}