<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SupplierController extends Controller
{
    public function index()
    {
        $lastSupplier = Supplier::orderBy('id', 'desc')->first();
        $newKodeSupplier = $lastSupplier
            ? sprintf('%03s', base_convert($lastSupplier->id + 1, 10, 36)) 
            : '001';

        $suppliers = Supplier::all();
        return view('master.suppliers', compact('suppliers', 'newKodeSupplier'));
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
            'kode_kategori' => 'required',
        ]);
        // Generate kode_supplier otomatis
        $lastSupplier = Supplier::orderBy('id', 'desc')->first();
        $newKodeSupplier = $lastSupplier ? str_pad($lastSupplier->id + 1, 4, '0', STR_PAD_LEFT) : '0001';

        // Tambahkan kode_supplier ke data yang akan disimpan
        $validated['kode_supplier'] = $newKodeSupplier;

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
            'kode_kategori' => 'required',
        ]);
        
        // Generate kode_supplier otomatis
        $lastSupplier = Supplier::orderBy('id', 'desc')->first();
        $newKodeSupplier = $lastSupplier ? str_pad($lastSupplier->id + 1, 4, '0', STR_PAD_LEFT) : '0001';
        
        // Tambahkan kode_supplier ke data yang akan disimpan
        $validated['kode_supplier'] = $newKodeSupplier;
        
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
            'kode_kategori' => 'required',
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
}