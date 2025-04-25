<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Pembelian;
use App\Models\PembelianItem;
use App\Models\Supplier;

class PembelianController extends Controller
{
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
            
            // Create purchase - FIXED THE COLUMN NAME HERE
            $pembelian = Pembelian::create([
                'nota' => $request->nota,
                'tanggal' => $request->tanggal,
                'kode_supplier' => $request->kode_supplier,
                'cabang' => $request->cabang, // Make sure this is included
                'pembayaran' => $request->pembayaran ?? 'Tunai',
                'cara_bayar' => $request->cara_bayar,
                'subtotal' => $request->subtotal,
                'diskon' => $request->diskon ?? 0,
                'ppn' => $request->ppn ?? 0,
                'grand_total' => $request->grand_total,
            ]);
            
            // Create purchase items
            foreach ($request->items as $item) {
                PembelianItem::create([
                    'nota' => $request->nota, // Gunakan nota sebagai foreign key
                    'kode_barang' => $item['kodeBarang'],
                    'nama_barang' => $item['namaBarang'],
                    'keterangan' => $item['keterangan'] ?? null,
                    'harga' => $item['harga'],
                    'qty' => $item['qty'],
                    'diskon' => $item['diskon'] ?? 0,
                    'total' => $item['total'],
                ]);
            }
            
            DB::commit();

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
        $purchases = Pembelian::with('items')->orderBy('created_at', 'desc')->get();

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
            
            // Create new purchase items
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