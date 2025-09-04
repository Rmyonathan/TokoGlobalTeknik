<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SupplierController extends Controller
{
    public function index(Request $request)
    {
        $searchBy = $request->input('search_by');
        $keyword = $request->input('search');
        $query = Supplier::query();

        if ($searchBy && $keyword) {
            if ($searchBy === 'kode_supplier') {
                $query->where('kode_supplier', 'like', "%{$keyword}%");
            } elseif ($searchBy === 'nama') {
                $query->where('nama', 'like', "%{$keyword}%");
            } elseif ($searchBy === 'alamat') {
                $query->where('alamat', 'like', "%{$keyword}%");
            } elseif ($searchBy === 'pemilik') {
                $query->where('pemilik', 'like', "%{$keyword}%");
            } elseif ($searchBy === 'telepon_fax') {
                $query->where('telepon_fax', 'like', "%{$keyword}%");
            } elseif ($searchBy === 'contact_person') {
                $query->where('contact_person', 'like', "%{$keyword}%");
            } elseif ($searchBy === 'hp_contact_person') {
                $query->where('hp_contact_person', 'like', "%{$keyword}%");
            } elseif ($searchBy === 'kode_grup_barang') {
                $query->where('kode_grup_barang', 'like', "%{$keyword}%");
            }
        } elseif ($keyword) {
            // Default: cari di nama dan kode_supplier
            $query->where(function($q) use ($keyword) {
                $q->where('nama', 'like', "%{$keyword}%")
                ->orWhere('kode_supplier', 'like', "%{$keyword}%");
            });
        }

        $suppliers = $query->orderBy('id', 'desc')->paginate(10)->withQueryString();

        $lastSupplier = Supplier::orderBy('id', 'desc')->first();
        $newKodeSupplier = $lastSupplier
            ? str_pad($lastSupplier->id + 1, 4, '0', STR_PAD_LEFT)
            : '0001';

        return view('master.suppliers', compact('suppliers', 'newKodeSupplier', 'searchBy', 'keyword'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required',
            'alamat' => 'required',
            'pemilik' => 'required',
            'telepon_fax' => 'required',
            'contact_person' => 'required',
            'hp_contact_person' => 'required',
            'kode_grup_barang' => 'required',
        ]);
        // Generate kode_supplier otomatis
        $lastSupplier = Supplier::orderBy('id', 'desc')->first();
        $newKodeSupplier = $lastSupplier ? str_pad($lastSupplier->id + 1, 4, '0', STR_PAD_LEFT) : '0001';

        // Tambahkan kode_supplier ke data yang akan disimpan
        $validated['kode_supplier'] = $newKodeSupplier;
        $validated['is_active'] = true; // Set default to active

        Supplier::create($validated);

        return redirect()->route('suppliers.index')->with('success', 'Supplier added successfully.');
    }

    public function storeAjax(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required',
            'alamat' => 'required',
            'pemilik' => 'required',
            'telepon_fax' => 'required',
            'contact_person' => 'required',
            'hp_contact_person' => 'required',
            'kode_grup_barang' => 'required',
        ]);
        
        // Generate kode_supplier otomatis
        $lastSupplier = Supplier::orderBy('id', 'desc')->first();
        $newKodeSupplier = $lastSupplier ? str_pad($lastSupplier->id + 1, 4, '0', STR_PAD_LEFT) : '0001';
        
        // Tambahkan kode_supplier ke data yang akan disimpan
        $validated['kode_supplier'] = $newKodeSupplier;
        $validated['is_active'] = true; // Set default to active
        
        $supplier = Supplier::create($validated);
        
        return redirect()->route('suppliers.index')->with('success', 'Supplier added successfully.');
    }

    public function update(Request $request, Supplier $supplier)
    {
        $validated = $request->validate([
            'kode_supplier' => 'required|unique:suppliers,kode_supplier,' . $supplier->id,
            'nama' => 'required',
            'alamat' => 'required',
            'pemilik' => 'required',
            'telepon_fax' => 'required',
            'contact_person' => 'required',
            'hp_contact_person' => 'required',
            'kode_grup_barang' => 'required',
        ]);

        $supplier->update($validated);
        return redirect()->route('suppliers.index')->with('success', 'Supplier updated successfully.');
    }

    public function destroy(Supplier $supplier)
    {
        $supplier->delete();
        return redirect()->route('suppliers.index')->with('success', 'Supplier deleted successfully.');
    }
    
    public function search(Request $request)
    {
        $keyword = $request->keyword;
        
        $suppliers = Supplier::where('kode_supplier', 'like', "%{$keyword}%")
            ->orWhere('nama', 'like', "%{$keyword}%")
            ->limit(10)
            ->get();
        
        return response()->json($suppliers);
    }

    /**
     * Toggle active status
     */
    public function toggleStatus(Supplier $supplier)
    {
        $supplier->update([
            'is_active' => !$supplier->is_active
        ]);

        $status = $supplier->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return redirect()->route('suppliers.index')
            ->with('success', "Supplier berhasil {$status}!");
    }
}