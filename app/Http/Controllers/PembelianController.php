<?php

namespace App\Http\Controllers;

use App\Models\KodeBarang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Pembelian;
use App\Models\PembelianItem;
use App\Models\Supplier;
use Illuminate\Support\Facades\Log;
use App\Models\Panel;



class PembelianController extends Controller
{
    protected $stockController;
    protected $panelController;

    public function __construct(StockController $stockController, PanelController $panelController)
    {
        $this->stockController = $stockController;
        $this->panelController = $panelController;
    }

    /**
     * Display the purchase transaction form.
     */
    public function index()
    {
        // Ambil nomor nota terakhir
        $lastPurchase = Pembelian::orderBy('created_at', 'desc')->first();

        // Generate nomor nota baru
        if ($lastPurchase) {
            // Ambil angka terakhir dari nota
            $lastNumber = (int) substr($lastPurchase->nota, strrpos($lastPurchase->nota, '-') + 1);
            $newNumber = $lastNumber + 1;
        } else {
            // Jika belum ada pembelian, mulai dari 1
            $newNumber = 1;
        }

        // Format nomor nota baru
        $currentMonth = date('m');
        $currentYear = date('y');
        $nota = "BL/{$currentMonth}/{$currentYear}-" . str_pad($newNumber, 5, '0', STR_PAD_LEFT);

        return view('pembelian.addpembelian', compact('nota'));
    }

    /**
     * Store a purchase transaction.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nota' => 'required|string|unique:pembelian,nota',
            'tanggal' => 'required|date',
            'kode_supplier' => 'required|exists:suppliers,kode_supplier',
            'cabang' => 'required|string',
            'subtotal' => 'required|numeric',
            'grand_total' => 'required|numeric',
            'items' => 'required|array',
            'items.*.kodeBarang' => 'required|string',
            'items.*.harga' => 'required|numeric',
            'items.*.qty' => 'required|numeric',
        ]);

        try {
            DB::beginTransaction();

            // Get supplier name for stock mutation record
            $supplier = Supplier::where('kode_supplier', $request->kode_supplier)->first();
            $supplierName = $supplier ? $supplier->nama : 'Unknown Supplier';

            // Create purchase
            $pembelian = Pembelian::create([
                'nota' => $request->nota,
                'tanggal' => $request->tanggal,
                'kode_supplier' => $request->kode_supplier,
                'cabang' => $request->cabang,
                'pembayaran' => $request->pembayaran ?? 'Tunai',
                'cara_bayar' => $request->cara_bayar,
                'subtotal' => $request->subtotal,
                'diskon' => $request->diskon ?? 0,
                'ppn' => $request->ppn ?? 0,
                'grand_total' => $request->grand_total,
            ]);

            // Get creator name from request or default to 'ADMIN'
            $creator = $request->created_by ?? 'ADMIN';

            // Format transaction number for mutation record
            $noTransaksi = "BL-" . date('m/y', strtotime($request->tanggal)) . "-" .
                           substr($request->nota, strrpos($request->nota, '-') + 1) .
                           " ({$creator})";

            // Create purchase items and update stock mutation
            foreach ($request->items as $item) {
                // Create purchase item
                PembelianItem::create([
                    'nota' => $request->nota,
                    'kode_barang' => $item['kodeBarang'],
                    'nama_barang' => $item['namaBarang'],
                    'keterangan' => $item['keterangan'] ?? null,
                    'harga' => $item['harga'],
                    'qty' => $item['qty'],
                    'diskon' => $item['diskon'] ?? 0,
                    'total' => $item['total'],
                ]);

                // Record purchase in stock mutation (just for reporting - doesn't affect panel inventory)
                $this->stockController->recordPurchase(
                    $item['kodeBarang'],
                    $item['namaBarang'],
                    $noTransaksi,
                    $request->tanggal,
                    $request->nota,
                    $supplierName . ' (' . $request->kode_supplier . ')',
                    $item['qty'],
                    $request->cabang,
                    'LBR'
                );
            }

            DB::commit();

            foreach ($request->items as $item) {
                $kode = KodeBarang::where('kode_barang', $item['kodeBarang'])->first();
                $name = $item['namaBarang'];
                $cost = $kode->cost;
                $price = $kode->price;
                $length = $kode->length;
                $group_id = $kode->kode_barang;
                $quantity = $item['qty'];
                $result = $this->panelController->addPanelsToInventory($name, $cost, $price, $length, $group_id, $quantity);
            }

            return response()->json([
                'id' => $pembelian->id,
                'nota' => $pembelian->nota,
                'tanggal' => $pembelian->tanggal,
                'supplier' => $pembelian->supplierRelation->nama ?? 'N/A',
                'grand_total' => $pembelian->grand_total,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Search for suppliers
     */
    public function searchSuppliers(Request $request)
    {
        $keyword = $request->keyword;

        $suppliers = Supplier::where('kode_supplier', 'like', "%{$keyword}%")
            ->orWhere('nama', 'like', "%{$keyword}%")
            ->limit(10)
            ->get();

        return response()->json($suppliers);
    }

    /**
     * Get purchase data
     */
    public function getPurchase($id)
    {
        $purchase = Pembelian::with('items')->findOrFail($id);

        return response()->json($purchase);
    }

    /**
     * Show the invoice (nota) for a purchase
     */
    public function showNota($id)
    {
        $purchase = Pembelian::with('items', 'supplierRelation')->findOrFail($id);

        return view('pembelian.nota_pembelian', compact('purchase'));
    }

    public function nota($nota)
    {
        $purchase = Pembelian::with('items', 'supplierRelation')->where('nota', $nota)->firstOrFail();
        return view('pembelian.nota_pembelian', compact('purchase'));
    }

    public function listNota()
    {
        // Fetch all purchases
        $purchases = Pembelian::with('items')
            ->orderBy('created_at', 'desc')
            ->paginate(5);

        return view('pembelian.lihat_nota_pembelian', compact('purchases'));
    }

    /**
     * Show the form for editing the specified purchase.
     */
    public function edit($id)
    {
        $purchase = Pembelian::with('items', 'supplierRelation')->findOrFail($id);

        // Get the supplier info
        $supplier = null;
        if ($purchase->supplierRelation) {
            $supplier = $purchase->kode_supplier . ' - ' . $purchase->supplierRelation->nama;
        }

        return view('pembelian.editpembelian', compact('purchase', 'supplier'));
    }

    /**
     * Update the specified purchase in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'tanggal' => 'required|date',
            'kode_supplier' => 'required|exists:suppliers,kode_supplier',
            'cabang' => 'required|string',
            'subtotal' => 'required|numeric',
            'grand_total' => 'required|numeric',
            'items' => 'required|array',
            'items.*.kodeBarang' => 'required|string',
            'items.*.harga' => 'required|numeric',
            'items.*.qty' => 'required|numeric',
        ]);

        try {
            DB::beginTransaction();

            // Find purchase
            $pembelian = Pembelian::findOrFail($id);
            $nota = $pembelian->nota; // Keep the original nota

            // Get supplier name for stock mutation record
            $supplier = Supplier::where('kode_supplier', $request->kode_supplier)->first();
            $supplierName = $supplier ? $supplier->nama : 'Unknown Supplier';

            // Get creator name from request or default to 'ADMIN'
            $creator = $request->updated_by ?? 'ADMIN';

            // Format transaction number for mutation record
            $noTransaksi = "BL-" . date('m/y', strtotime($request->tanggal)) . "-" .
                           substr($nota, strrpos($nota, '-') + 1) .
                           " ({$creator}) [UPDATED]";

            // First, reverse all previous stock movements from this purchase
            // Get the original items
            $originalItems = PembelianItem::where('nota', $nota)->get();
            foreach ($originalItems as $item) {
                // Record sale to reverse the original purchase in stock mutation
                $this->stockController->recordSale(
                    $item->kode_barang,
                    $item->nama_barang,
                    $noTransaksi,
                    now(), // Use current date/time for the reversal
                    $nota . ' (reversal)',
                    $supplierName . ' (' . $request->kode_supplier . ')',
                    $item->qty, // Same quantity as purchase, but as a "sale" to reduce stock
                    $pembelian->cabang,
                    'LBR'
                );
            }

            // Update purchase
            $pembelian->update([
                'tanggal' => $request->tanggal,
                'kode_supplier' => $request->kode_supplier,
                'cabang' => $request->cabang,
                'pembayaran' => $request->pembayaran ?? 'Tunai',
                'cara_bayar' => $request->cara_bayar,
                'subtotal' => $request->subtotal,
                'diskon' => $request->diskon ?? 0,
                'ppn' => $request->ppn ?? 0,
                'grand_total' => $request->grand_total,
            ]);

            // Delete all existing items
            PembelianItem::where('nota', $nota)->delete();

            // Create new purchase items and stock movements
            foreach ($request->items as $item) {
                PembelianItem::create([
                    'nota' => $nota,
                    'kode_barang' => $item['kodeBarang'],
                    'nama_barang' => $item['namaBarang'],
                    'keterangan' => $item['keterangan'] ?? null,
                    'harga' => $item['harga'],
                    'qty' => $item['qty'],
                    'diskon' => $item['diskon'] ?? 0,
                    'total' => $item['total'],
                ]);

                // Record new purchase in stock mutation
                $this->stockController->recordPurchase(
                    $item['kodeBarang'],
                    $item['namaBarang'],
                    $noTransaksi,
                    $request->tanggal,
                    $nota . ' (updated)',
                    $supplierName . ' (' . $request->kode_supplier . ')',
                    $item['qty'],
                    $request->cabang,
                    'LBR'
                );
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'id' => $pembelian->id,
                'nota' => $pembelian->nota,
                'message' => 'Pembelian berhasil diperbarui'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified purchase from storage.
     */
    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            // Find purchase
            $pembelian = Pembelian::findOrFail($id);
            $nota = $pembelian->nota;

            // Get supplier name for stock mutation record
            $supplier = $pembelian->supplierRelation;
            $supplierName = $supplier ? $supplier->nama : 'Unknown Supplier';

            // Get creator name or default to 'ADMIN'
            $creator = 'ADMIN';

// Format transaction number for deletion record
            $noTransaksi = "BL-" . date('m/y', strtotime($pembelian->tanggal)) . "-" .
              substr($nota, strrpos($nota, '-') + 1) .
              " ({$creator}) [DELETED]";

            // Get the items to reverse stock movements
            $items = PembelianItem::where('nota', $nota)->get();

            foreach ($items as $item) {
                // Record sale to reverse the purchase in stock mutation
                $this->stockController->recordSale(
                    $item->kode_barang,
                    $item->nama_barang,
                    $noTransaksi,
                    now(), // Use current date/time for the deletion
                    $nota . ' (deleted)',
                    $supplierName . ' (' . $pembelian->kode_supplier . ')',
                    $item->qty, // Same quantity as purchase, but as a "sale" to reduce stock
                    $pembelian->cabang,
                    'LBR'
                );
            }

            // Delete all related items first
            PembelianItem::where('nota', $nota)->delete();

            // Delete the purchase
            $pembelian->delete();

            DB::commit();

            return redirect()->route('pembelian.nota.list')
                ->with('success', 'Nota pembelian berhasil dihapus');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->route('pembelian.nota.list')
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}
