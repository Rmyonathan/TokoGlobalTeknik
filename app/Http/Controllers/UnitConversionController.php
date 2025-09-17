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
     * Return list of unit conversions as JSON (for inline management)
     */
    public function list($kodeBarangId)
    {
        $items = UnitConversion::where('kode_barang_id', $kodeBarangId)
            ->orderBy('unit_turunan')
            ->get(['id','unit_turunan','nilai_konversi','is_active']);
        return response()->json($items);
    }

    /**
     * List unit conversions by kode_barang string (for panels/edit view)
     */
    public function listByKode(string $kodeBarang)
    {
        $kb = KodeBarang::where('kode_barang', $kodeBarang)->first();
        if (!$kb) {
            return response()->json(['error' => 'Kode barang tidak ditemukan'], 404);
        }
        $items = UnitConversion::where('kode_barang_id', $kb->id)
            ->orderBy('unit_turunan')
            ->get(['id','unit_turunan','nilai_konversi','is_active']);
        return response()->json(['kode_barang_id' => $kb->id, 'items' => $items]);
    }

    /**
     * Store unit conversion by kode_barang string
     */
    public function storeByKode(Request $request, string $kodeBarang)
    {
        $kb = KodeBarang::where('kode_barang', $kodeBarang)->first();
        if (!$kb) {
            return response()->json(['error' => 'Kode barang tidak ditemukan'], 404);
        }
        $validator = Validator::make($request->all(), [
            'unit_turunan' => 'required|string|max:20',
            'nilai_konversi' => 'required|integer|min:1',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        UnitConversion::updateOrCreate(
            ['kode_barang_id' => $kb->id, 'unit_turunan' => strtoupper($request->unit_turunan)],
            ['nilai_konversi' => (int) $request->nilai_konversi, 'is_active' => true]
        );
        return response()->json(['success' => true]);
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
            // Check if this is an AJAX request
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }
            return back()->withErrors($validator)->withInput();
        }

        // Check if unit already exists for this item
        $existingUnit = UnitConversion::where('kode_barang_id', $kodeBarangId)
            ->where('unit_turunan', $request->unit_turunan)
            ->first();

        if ($existingUnit) {
            // Check if this is an AJAX request
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'errors' => ['unit_turunan' => 'Satuan ini sudah ada untuk barang ini']
                ], 422);
            }
            return back()->withErrors(['unit_turunan' => 'Satuan ini sudah ada untuk barang ini'])->withInput();
        }

        $unitConversion = UnitConversion::create([
            'kode_barang_id' => $kodeBarangId,
            'unit_turunan' => $request->unit_turunan,
            'nilai_konversi' => $request->nilai_konversi,
            'keterangan' => $request->keterangan,
            'is_active' => true,
        ]);

        // Check if this is an AJAX request
        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Satuan konversi berhasil ditambahkan!',
                'data' => $unitConversion
            ]);
        }

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
        
        // Check if this is an AJAX request
        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Satuan konversi berhasil {$status}!",
                'is_active' => $unitConversion->is_active
            ]);
        }
        
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
        
        // Check if this is an AJAX request
        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Satuan konversi berhasil dihapus!'
            ]);
        }
        
        return redirect()->route('unit_conversion.index', $kodeBarangId)
            ->with('success', 'Satuan konversi berhasil dihapus!');
    }

    /**
     * Update unit conversion by kode barang string
     */
    public function updateByKode(Request $request, $kodeBarang, $id)
    {
        // Find the kode barang by string
        $kodeBarangModel = KodeBarang::where('kode_barang', $kodeBarang)->firstOrFail();
        
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
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Check if unit already exists for this item (excluding current record)
        $existingUnit = UnitConversion::where('kode_barang_id', $kodeBarangModel->id)
            ->where('unit_turunan', $request->unit_turunan)
            ->where('id', '!=', $id)
            ->first();

        if ($existingUnit) {
            return response()->json([
                'success' => false,
                'errors' => ['unit_turunan' => 'Satuan ini sudah ada untuk barang ini']
            ], 422);
        }

        $unitConversion = UnitConversion::where('kode_barang_id', $kodeBarangModel->id)
            ->findOrFail($id);
            
        $unitConversion->update([
            'unit_turunan' => $request->unit_turunan,
            'nilai_konversi' => $request->nilai_konversi,
            'keterangan' => $request->keterangan,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Satuan konversi berhasil diupdate!',
            'data' => $unitConversion
        ]);
    }

    /**
     * Toggle unit conversion status by kode barang string
     */
    public function toggleByKode($kodeBarang, $id)
    {
        // Find the kode barang by string
        $kodeBarangModel = KodeBarang::where('kode_barang', $kodeBarang)->firstOrFail();
        
        $unitConversion = UnitConversion::where('kode_barang_id', $kodeBarangModel->id)
            ->findOrFail($id);
            
        $unitConversion->update(['is_active' => !$unitConversion->is_active]);
        
        $status = $unitConversion->is_active ? 'diaktifkan' : 'dinonaktifkan';
        
        return response()->json([
            'success' => true,
            'message' => "Satuan konversi berhasil {$status}!",
            'is_active' => $unitConversion->is_active
        ]);
    }

    /**
     * Delete unit conversion by kode barang string
     */
    public function destroyByKode($kodeBarang, $id)
    {
        // Find the kode barang by string
        $kodeBarangModel = KodeBarang::where('kode_barang', $kodeBarang)->firstOrFail();
        
        $unitConversion = UnitConversion::where('kode_barang_id', $kodeBarangModel->id)
            ->findOrFail($id);
            
        $unitConversion->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Satuan konversi berhasil dihapus!'
        ]);
    }

    /**
     * Bulk assign a unit conversion definition to multiple KodeBarang
     * Payload: unit_turunan, nilai_konversi, item_ids[]
     */
    public function bulkAssign(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'unit_turunan' => 'required|string|max:20',
            'nilai_konversi' => 'required|integer|min:1',
            'item_ids' => 'required|array|min:1',
            'item_ids.*' => 'integer|exists:kode_barangs,id'
        ], [
            'unit_turunan.required' => 'Satuan turunan harus diisi',
            'nilai_konversi.required' => 'Nilai konversi harus diisi',
            'item_ids.required' => 'Pilih minimal satu barang'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $created = 0; $updated = 0;
        foreach ($request->item_ids as $kodeBarangId) {
            $uc = UnitConversion::updateOrCreate(
                [ 'kode_barang_id' => $kodeBarangId, 'unit_turunan' => strtoupper($request->unit_turunan) ],
                [ 'nilai_konversi' => (int) $request->nilai_konversi, 'is_active' => true ]
            );
            if ($uc->wasRecentlyCreated) { $created++; } else { $updated++; }
        }

        return response()->json([
            'success' => true,
            'message' => "Bulk assign selesai. Ditambahkan: {$created}, Diperbarui: {$updated}",
            'created' => $created,
            'updated' => $updated
        ]);
    }

    /**
     * Get all items for a given unit_turunan, with selection info
     * Query: ?unit=LUSIN
     */
    public function itemsByUnit(Request $request)
    {
        $unit = strtoupper(trim($request->query('unit', '')));
        if ($unit === '') {
            return response()->json(['success' => false, 'message' => 'Param unit wajib diisi'], 400);
        }

        // Items having this unit conversion
        $withUnit = UnitConversion::where('unit_turunan', $unit)->pluck('kode_barang_id')->toArray();
        $items = KodeBarang::orderBy('name')->get(['id','kode_barang','name','unit_dasar']);

        // Fetch all conversions for these items to show existing satuan besar list
        $itemIds = $items->pluck('id')->toArray();
        $conversions = UnitConversion::whereIn('kode_barang_id', $itemIds)
            ->orderBy('unit_turunan')
            ->get(['kode_barang_id','unit_turunan','nilai_konversi'])
            ->groupBy('kode_barang_id');

        $result = $items->map(function($it) use ($withUnit, $conversions) {
            $convList = ($conversions->get($it->id) ?? collect())->map(function($uc){
                return [
                    'unit' => $uc->unit_turunan,
                    'nilai' => (float) $uc->nilai_konversi,  // FIX: Gunakan float untuk desimal
                ];
            })->values()->all();
            return [
                'id' => $it->id,
                'kode_barang' => $it->kode_barang,
                'name' => $it->name,
                'unit_dasar' => $it->unit_dasar,
                'conversions' => $convList,
                'selected' => in_array($it->id, $withUnit)
            ];
        });

        return response()->json(['success' => true, 'data' => $result]);
    }

    /**
     * Bulk sync items for a unit: ensure selected list has the unit with value, and others do not
     * Payload: unit_turunan, nilai_konversi, item_ids[]
     */
    public function bulkSyncByUnit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'unit_turunan' => 'required|string|max:20',
            'nilai_konversi' => 'required|integer|min:1',
            'item_ids' => 'nullable|array',
            'item_ids.*' => 'integer|exists:kode_barangs,id'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $unit = strtoupper($request->unit_turunan);
        $nilai = (int) $request->nilai_konversi;
        $selectedIds = $request->input('item_ids', []);

        // Remove unit from all items not in selected list
        $allWithUnit = UnitConversion::where('unit_turunan', $unit)->pluck('kode_barang_id')->toArray();
        $toRemove = array_diff($allWithUnit, $selectedIds);
        if (!empty($toRemove)) {
            UnitConversion::whereIn('kode_barang_id', $toRemove)->where('unit_turunan', $unit)->delete();
        }

        // Upsert for selected
        $created = 0; $updated = 0;
        foreach ($selectedIds as $id) {
            $uc = UnitConversion::updateOrCreate(
                [ 'kode_barang_id' => $id, 'unit_turunan' => $unit ],
                [ 'nilai_konversi' => $nilai, 'is_active' => true ]
            );
            if ($uc->wasRecentlyCreated) { $created++; } else { $updated++; }
        }

        return response()->json([
            'success' => true,
            'message' => 'Sinkronisasi berhasil',
            'created' => $created,
            'updated' => $updated,
            'removed' => count($toRemove)
        ]);
    }
}
