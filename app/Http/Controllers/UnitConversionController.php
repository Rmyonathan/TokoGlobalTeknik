<?php

namespace App\Http\Controllers;

use App\Models\UnitConversion;
use App\Models\KodeBarang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UnitConversionController extends Controller
{
    /**
     * Display a listing of unit conversions for a specific item
     */
    public function index($kodeBarangId)
    {
        $kodeBarang = KodeBarang::findOrFail($kodeBarangId);
        $unitConversions = UnitConversion::where('kode_barang_id', $kodeBarangId)
            ->orderBy('unit_turunan')
            ->get();
            
        return view('master.unit_conversion.index', compact('kodeBarang', 'unitConversions'));
    }

    /**
     * Show the form for creating a new unit conversion
     */
    public function create($kodeBarangId)
    {
        $kodeBarang = KodeBarang::findOrFail($kodeBarangId);
        return view('master.unit_conversion.create', compact('kodeBarang'));
    }

    /**
     * Store a newly created unit conversion
     */
    public function store(Request $request, $kodeBarangId)
    {
        $validator = Validator::make($request->all(), [
            'unit_turunan' => 'required|string|max:20',
            'nilai_konversi' => 'required|integer|min:1',
            'keterangan' => 'nullable|string|max:255',
        ], [
            'unit_turunan.required' => 'Satuan turunan harus diisi',
            'unit_turunan.max' => 'Satuan turunan maksimal 20 karakter',
            'nilai_konversi.required' => 'Nilai konversi harus diisi',
            'nilai_konversi.integer' => 'Nilai konversi harus berupa angka bulat',
            'nilai_konversi.min' => 'Nilai konversi minimal 1',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Check if unit already exists for this item
        $existingUnit = UnitConversion::where('kode_barang_id', $kodeBarangId)
            ->where('unit_turunan', $request->unit_turunan)
            ->first();

        if ($existingUnit) {
            return back()->withErrors(['unit_turunan' => 'Satuan ini sudah ada untuk barang ini'])->withInput();
        }

        UnitConversion::create([
            'kode_barang_id' => $kodeBarangId,
            'unit_turunan' => $request->unit_turunan,
            'nilai_konversi' => $request->nilai_konversi,
            'keterangan' => $request->keterangan,
            'is_active' => true,
        ]);

        return redirect()->route('unit_conversion.index', $kodeBarangId)
            ->with('success', 'Satuan konversi berhasil ditambahkan!');
    }

    /**
     * Show the form for editing a unit conversion
     */
    public function edit($kodeBarangId, $id)
    {
        $kodeBarang = KodeBarang::findOrFail($kodeBarangId);
        $unitConversion = UnitConversion::where('kode_barang_id', $kodeBarangId)
            ->findOrFail($id);
            
        return view('master.unit_conversion.edit', compact('kodeBarang', 'unitConversion'));
    }

    /**
     * Update the specified unit conversion
     */
    public function update(Request $request, $kodeBarangId, $id)
    {
        // dd('test');
        $validator = Validator::make($request->all(), [
            'unit_turunan' => 'required|string|max:20',
            'nilai_konversi' => 'required|integer|min:1',
            'keterangan' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $unitConversion = UnitConversion::where('kode_barang_id', $kodeBarangId)
            ->findOrFail($id);

        // Check if unit already exists for this item (excluding current record)
        $existingUnit = UnitConversion::where('kode_barang_id', $kodeBarangId)
            ->where('unit_turunan', $request->unit_turunan)
            ->where('id', '!=', $id)
            ->first();

        if ($existingUnit) {
            return back()->withErrors(['unit_turunan' => 'Satuan ini sudah ada untuk barang ini'])->withInput();
        }
        // dd($unitConversion);
        $unitConversion->update([
            'unit_turunan' => $request->unit_turunan,
            'nilai_konversi' => $request->nilai_konversi,
            'keterangan' => $request->keterangan,
        ]);

        return redirect()->route('unit_conversion.index', $kodeBarangId)
            ->with('success', 'Satuan konversi berhasil diupdate!');
    }

    /**
     * Toggle active status of unit conversion
     */
    public function toggleStatus($kodeBarangId, $id)
    {
        $unitConversion = UnitConversion::where('kode_barang_id', $kodeBarangId)
            ->findOrFail($id);
            
        $unitConversion->update(['is_active' => !$unitConversion->is_active]);
        
        $status = $unitConversion->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return back()->with('success', "Satuan konversi berhasil {$status}!");
    }

    /**
     * Remove the specified unit conversion
     */
    public function destroy($kodeBarangId, $id)
    {
        $unitConversion = UnitConversion::where('kode_barang_id', $kodeBarangId)
            ->findOrFail($id);
            
        $unitConversion->delete();
        
        return redirect()->route('unit_conversion.index', $kodeBarangId)
            ->with('success', 'Satuan konversi berhasil dihapus!');
    }
}
