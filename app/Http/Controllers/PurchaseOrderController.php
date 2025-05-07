<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use Illuminate\Support\Facades\DB;

class PurchaseOrderController extends Controller
{
    private function generateNoPO()
    {
        $now = now();
        $prefix = 'PO-' . $now->format('my'); // ex: PO-0625

        $latestPO = PurchaseOrder::whereRaw("DATE_FORMAT(tanggal, '%m%y') = ?", [$now->format('my')])
            ->orderBy('no_po', 'desc')
            ->first();

        $lastNumber = 0;

        if ($latestPO) {
            // ambil 5 digit terakhir
            $lastNumber = (int) substr($latestPO->no_po, -5);
        }

        $newNumber = str_pad($lastNumber + 1, 5, '0', STR_PAD_LEFT); // padding nol depan
        return $prefix . '-' . $newNumber;
    }


    public function index()
    {
        $purchaseOrders = PurchaseOrder::latest()->get();
        return view('transaksi.purchaseorder', compact('purchaseOrders'));
    }

    public function show($id)
    {
        $po = PurchaseOrder::with(['items', 'customer'])->findOrFail($id);
        return view('transaksi.purchaseorder_detail', compact('po'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'tanggal' => 'required|date',
            'kode_customer' => 'required|exists:customers,kode_customer',
            'sales' => 'required|exists:stok_owners,kode_stok_owner',
            'subtotal' => 'required|numeric',
            'grand_total' => 'required|numeric',
            'items' => 'required|array|min:1',
            'items.*.kodeBarang' => 'required|exists:panels,group_id',
            'items.*.harga' => 'required|numeric',
            'items.*.qty' => 'required|numeric|min:1',
            // Tambahin validasi lain sesuai kebutuhan
        ]);        

        DB::beginTransaction();

        try {
            $po = PurchaseOrder::create([
                'no_po' => $this->generateNoPO(),
                'tanggal' => now(), // tanggal saat ini
                'kode_customer' => $request->kode_customer,
                'sales' => $request->sales,
                'lokasi' => $request->lokasi,
                'pembayaran' => $request->pembayaran,
                'cara_bayar' => $request->cara_bayar,
                'tanggal_jadi' => null,
                'subtotal' => $request->subtotal,
                'discount' => $request->discount ?? 0,
                'disc_rupiah' => $request->disc_rupiah ?? 0,
                'ppn' => $request->ppn,
                'dp' => $request->dp ?? 0,
                'grand_total' => $request->grand_total,
                'status' => 'pending',
            ]);

            foreach ($request->items as $item) {
                $po->items()->create([
                    'kode_barang' => $item['kodeBarang'],
                    'nama_barang' => $item['namaBarang'],
                    'keterangan' => $item['keterangan'],
                    'harga' => $item['harga'],
                    'panjang' => $item['panjang'] ?? 0,
                    'qty' => $item['qty'],
                    'total' => $item['total'],
                    'diskon' => $item['diskon'] ?? 0,
                ]);
            }

            DB::commit();

            return response()->json(['status' => 'success', 'message' => 'Purchase Order created successfully.']);

        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function complete($id)
    {
        $po = PurchaseOrder::findOrFail($id);
        $po->update([
            'status' => 'completed',
            'tanggal_jadi' => now()
        ]);

        // nanti bisa tambahin proses mutasi stok dll di sini
        return redirect()->back()->with('success', 'PO berhasil diselesaikan.');
    }

    public function cancel($id)
    {
        $po = PurchaseOrder::findOrFail($id);
        if ($po->status === 'pending') {
            $po->update(['status' => 'cancelled']);
        }

        return redirect()->route('transaksi.purchaseorder')->with('success', 'PO dibatalkan.');
}

}
