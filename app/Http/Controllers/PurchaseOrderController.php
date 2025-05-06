<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use Illuminate\Support\Facades\DB;

class PurchaseOrderController extends Controller
{
    public function index()
    {
        $purchaseOrders = PurchaseOrder::latest()->get();
        return view('purchase_order.index', compact('purchaseOrders'));
    }

    public function show($id)
    {
        $po = PurchaseOrder::with('items')->findOrFail($id);
        return view('purchase_order.show', compact('po'));
    }

    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
            $po = PurchaseOrder::create([
                'no_po' => $request->no_po,
                'tanggal' => now(), // tanggal saat ini
                'kode_customer' => $request->kode_customer,
                'sales' => $request->sales,
                'lokasi' => $request->lokasi,
                'pembayaran' => $request->pembayaran,
                'cara_bayar' => $request->cara_bayar,
                'tanggal_jadi' => null,
                'subtotal' => $request->subtotal,
                'discount' => $request->discount,
                'disc_rupiah' => $request->disc_rupiah,
                'ppn' => $request->ppn,
                'dp' => $request->dp,
                'grand_total' => $request->grand_total,
                'status' => 'pending',
            ]);

            foreach ($request->items as $item) {
                $po->items()->create([
                    'kode_barang' => $item['kode_barang'],
                    'nama_barang' => $item['nama_barang'],
                    'keterangan' => $item['keterangan'],
                    'harga' => $item['harga'],
                    'length' => $item['length'],
                    'qty' => $item['qty'],
                    'total' => $item['total'],
                    'diskon' => $item['diskon'],
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
}
