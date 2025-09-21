<?php

namespace App\Http\Controllers;

use App\Models\KodeBarang;
use App\Http\Requests\StoreKodeBarangRequest;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\UpdateKodeBarangRequest;
use Illuminate\Http\Request;

class KodeBarangController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function createCode()
    {
        // Ambil nama grup dari tabel grup_barang yang sudah dibuat
        $group_names = \App\Models\GrupBarang::where('status', 'Active')
            ->orderBy('name')
            ->pluck('name');
            
        return view('panels.add-code', compact('group_names'));
    }

    public function viewCode()
    {
        //
        $codes = KodeBarang::paginate(10);

        return view('panels.view-code', compact('codes'));
    }

    /**
     * Import Form for Kode Barang (CSV as Excel alternative)
     */
    public function importForm()
    {
        // Provide sample headers to the view
        $sampleHeaders = [
            'kode_barang', 'name', 'attribute', 'merek', 'ukuran', 'unit_dasar',
            'satuan_dasar', 'satuan_besar', 'nilai_konversi', 'harga_jual', 'ongkos_kuli_default'
        ];
        return view('panels.import-code', compact('sampleHeaders'));
    }

    /**
     * Process uploaded CSV and create/update KodeBarang records
     */
    public function importProcess(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt',
        ]);

        $path = $request->file('file')->getRealPath();
        $handle = fopen($path, 'r');
        if (!$handle) {
            return back()->withErrors(['file' => 'Tidak bisa membaca file']);
        }

        $header = null;
        $rows = [];
        while (($row = fgetcsv($handle, 0, ',')) !== false) {
            if ($header === null) {
                $header = array_map('trim', $row);
                continue;
            }
            $rows[] = array_combine($header, $row);
        }
        fclose($handle);

        $created = 0; $updated = 0; $errors = [];
        foreach ($rows as $index => $data) {
            try {
                $kode = trim($data['kode_barang'] ?? '');
                $name = trim($data['name'] ?? '');
                if ($kode === '' || $name === '') {
                    throw new \Exception('kode_barang dan name wajib diisi');
                }

                $payload = [
                    'kode_barang' => $kode,
                    'name' => $name,
                    'attribute' => $data['attribute'] ?? null,
                    'merek' => $data['merek'] ?? null,
                    'ukuran' => $data['ukuran'] ?? null,
                    'unit_dasar' => $data['unit_dasar'] ?? 'PCS',
                    'satuan_dasar' => $data['satuan_dasar'] ?? null,
                    'satuan_besar' => $data['satuan_besar'] ?? null,
                    'nilai_konversi' => isset($data['nilai_konversi']) ? (int) $data['nilai_konversi'] : null,
                    'harga_jual' => isset($data['harga_jual']) ? (float) $data['harga_jual'] : 0,
                    'ongkos_kuli_default' => isset($data['ongkos_kuli_default']) ? (float) $data['ongkos_kuli_default'] : 0,
                    'status' => 'Active',
                    'cost' => 0,
                ];

                // Map grup automatically by attribute if exists
                if (!empty($payload['attribute'])) {
                    $grup = \App\Models\GrupBarang::where('name', $payload['attribute'])->first();
                    if ($grup) {
                        $payload['grup_barang_id'] = $grup->id;
                    }
                }

                $existing = KodeBarang::where('kode_barang', $kode)->first();
                if ($existing) {
                    $existing->update($payload);
                    $updated++;
                } else {
                    $existing = KodeBarang::create($payload);
                    $created++;
                }

                // Create default conversion if provided
                if (!empty($payload['satuan_besar']) && !empty($payload['nilai_konversi'])) {
                    \App\Models\UnitConversion::updateOrCreate(
                        [ 'kode_barang_id' => $existing->id, 'unit_turunan' => strtoupper($payload['satuan_besar']) ],
                        [ 'nilai_konversi' => (int) $payload['nilai_konversi'], 'is_active' => true ]
                    );
                }
            } catch (\Throwable $e) {
                $errors[] = 'Baris ' . ($index + 2) . ': ' . $e->getMessage();
            }
        }

        if (!empty($errors)) {
            return back()->with('warning', "Selesai dengan peringatan. Created: {$created}, Updated: {$updated}")
                ->withErrors(['detail' => implode("\n", $errors)]);
        }

        return redirect()->route('code.view-code')
            ->with('success', "Import selesai. Created: {$created}, Updated: {$updated}");
    }

    /**
     * Show the form for creating a new resource.
     */
    public function storeCode(Request $request)
    {
        // Validate the request
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            // 'cost' dihapus dari form, akan diset default 0
            'price' => 'nullable|numeric|min:0',
            'attribute' => 'required|string|max:255',
            'merek' => 'nullable|string|max:255',
            'ukuran' => 'nullable|string|max:100',
            'kode_barang' => 'required|string|max:255',
            'unit_dasar' => 'required|string|max:20',
            'satuan_dasar' => 'nullable|string|max:50',
            'satuan_besar' => 'nullable|string|max:50',
            'nilai_konversi' => 'nullable|integer|min:1',
            'harga_jual' => 'nullable|numeric|min:0',
            'ongkos_kuli_default' => 'nullable|numeric|min:0',
            // 'length' => 'required|numeric|min:0.1',
        ], [
            'kode_barang.required' => 'Item code is required',
            'kode_barang.string' => 'Item code must be a valid string',
            'kode_barang.max' => 'Item code may not be greater than 255 characters',
            'attribute.required' => 'Panel name is required',
            'attribute.string' => 'Panel name must be a valid string',
            'attribute.max' => 'Panel name may not be greater than 255 characters',
            // 'length.required' => 'Panel length is required',
            // 'length.numeric' => 'Panel length must be a number',
            // 'length.min' => 'Panel length must be at least 0.1 meters',
            'name.required' => 'Panel name is required',
            'name.string' => 'Panel name must be a valid string',
            'name.max' => 'Panel name may not be greater than 255 characters',
            // validasi cost dihapus
            'price.required' => 'Price is required',
            'price.numeric' => 'Price must be a valid number',
            'price.min' => 'Price must be at least 0',
            'unit_dasar.required' => 'Satuan dasar harus diisi',
            'unit_dasar.max' => 'Satuan dasar maksimal 20 karakter',
            'harga_jual.required' => 'Harga jual harus diisi',
            'harga_jual.numeric' => 'Harga jual harus berupa angka',
            'harga_jual.min' => 'Harga jual minimal 0',
            'ongkos_kuli_default.numeric' => 'Ongkos kuli harus berupa angka',
            'ongkos_kuli_default.min' => 'Ongkos kuli minimal 0',
        ]);

        // Check if the kode_barang already exists
        if (KodeBarang::where('kode_barang', $validated['kode_barang'])->exists()) {
            // Log the error if kode_barang already exists
            Log::error('Duplicate kode_barang attempt: ' . $validated['kode_barang']);
            
            // Return a response with a custom error message
            return back()->withErrors(['kode_barang' => 'Kode barang ini sudah digunakan untuk barang lain, Please choose another one']);
        }

        $validated['status'] = 'Active';
        $validated['cost'] = 0; // set default cost dari master barang
        
        // Set default values if not provided
        // $validated['unit_dasar'] = $validated['unit_dasar'] ?? 'LBR';
        // Default conversion: PCS -> LUSIN (12) if unit dasar PCS
        // if (($validated['unit_dasar'] ?? '') === 'PCS') {
        //     $validated['satuan_dasar'] = 'PCS';
        //     $validated['satuan_besar'] = 'LUSIN';
        //     $validated['nilai_konversi'] = $validated['nilai_konversi'] ?? 12;
        // }
        $validated['price'] = $validated['price'] ?? 0;
        $validated['harga_jual'] = $validated['harga_jual'] ?? $validated['price'];
        $validated['ongkos_kuli_default'] = $validated['ongkos_kuli_default'] ?? 0;

        // Cari grup barang berdasarkan attribute yang dipilih
        $grupBarang = \App\Models\GrupBarang::where('name', $validated['attribute'])->first();
        if ($grupBarang) {
            $validated['grup_barang_id'] = $grupBarang->id;
        }

        // Create the new KodeBarang
        $kodeBarang = KodeBarang::create($validated);

        // Auto-create default conversion when unit dasar is PCS
        // if (($validated['unit_dasar'] ?? '') === 'PCS') {
        //     $defaultSatuanBesar = $validated['satuan_besar'] ?? 'LUSIN';
        //     $defaultNilai = (int) ($validated['nilai_konversi'] ?? 12);
        //     if ($defaultSatuanBesar && $defaultNilai > 0) {
        //         \App\Models\UnitConversion::updateOrCreate(
        //             [ 'kode_barang_id' => $kodeBarang->id, 'unit_turunan' => strtoupper($defaultSatuanBesar) ],
        //             [ 'nilai_konversi' => $defaultNilai, 'is_active' => true ]
        //         );
        //     }
        // }

        // Handle inline unit conversions payload (JSON array)
        $ucPayload = $request->input('unit_conversions');
        if ($ucPayload) {
            $items = json_decode($ucPayload, true);
            if (is_array($items)) {
                foreach ($items as $it) {
                    if (!empty($it['unit_turunan']) && !empty($it['nilai_konversi'])) {
                        \App\Models\UnitConversion::updateOrCreate(
                            [ 'kode_barang_id' => $kodeBarang->id, 'unit_turunan' => strtoupper($it['unit_turunan']) ],
                            [ 'nilai_konversi' => (int) $it['nilai_konversi'], 'is_active' => true ]
                        );
                    }
                }
            }
        }

        return redirect()->route('code.view-code')
            ->with('success', "Successfully added group code!");
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreKodeBarangRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(KodeBarang $kodeBarang)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $viewPath = resource_path('views/panels/edit-code.blade.php');
        if (file_exists($viewPath)) {
            $code = KodeBarang::findOrFail($id);
            
            // Ambil nama grup dari tabel grup_barang yang sudah dibuat
            $group_names = \App\Models\GrupBarang::where('status', 'Active')
                ->orderBy('name')
                ->pluck('name');
                
            return view('panels.edit-code', compact('code', 'group_names'));
        } else {
            return "View file does not exist at: " . $viewPath;
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        // Validate the request
        $validated = $request->validate([
            'grup_barang_id' => 'required|string|max:255',
            'attribute' => 'required|string|max:255',
            'merek' => 'nullable|string|max:255',
            'ukuran' => 'nullable|string|max:100',
            'kode_barang' => 'required|string|max:255',
            'unit_dasar' => 'required|string|max:20',
            'harga_jual' => 'required|numeric|min:0',
            'ongkos_kuli_default' => 'nullable|numeric|min:0',
            // 'length' => 'required|numeric|min:0.1',
        ], [
            'grup_barang_id.required' => 'Grup barang harus dipilih',
            'grup_barang_id.string' => 'Grup barang harus berupa string',
            'grup_barang_id.max' => 'Grup barang maksimal 255 karakter',
            
            'kode_barang.required' => 'Item code is required',
            'kode_barang.string' => 'Item code must be a valid string',
            'kode_barang.max' => 'Item code may not be greater than 255 characters',

            'attribute.required' => 'Panel name is required',
            'attribute.string' => 'Panel name must be a valid string',
            'attribute.max' => 'Panel name may not be greater than 255 characters',
            'unit_dasar.required' => 'Satuan dasar harus diisi',
            'unit_dasar.max' => 'Satuan dasar maksimal 20 karakter',
            'harga_jual.required' => 'Harga jual harus diisi',
            'harga_jual.numeric' => 'Harga jual harus berupa angka',
            'harga_jual.min' => 'Harga jual minimal 0',
            'ongkos_kuli_default.numeric' => 'Ongkos kuli harus berupa angka',
            'ongkos_kuli_default.min' => 'Ongkos kuli minimal 0',

            // 'length.required' => 'Panel length is required',
            // 'length.numeric' => 'Panel length must be a number',
            // 'length.min' => 'Panel length must be at least 0.1 meters',
        ]);

        $code = KodeBarang::findOrFail($id);
        
        // Cari grup barang berdasarkan nama yang dipilih
        $grupBarang = \App\Models\GrupBarang::where('name', $validated['grup_barang_id'])->first();
        if ($grupBarang) {
            $validated['kategori_id'] = $grupBarang->id;
        }
        
        // Update attribute berdasarkan grup barang yang dipilih
        $validated['attribute'] = $validated['grup_barang_id'];
        
        // Hapus grup_barang_id dari data yang akan diupdate
        unset($validated['grup_barang_id']);
        
        $code->update($validated);

        // Sync default conversion if unit dasar is PCS
        if (($validated['unit_dasar'] ?? $code->unit_dasar) === 'PCS') {
            $defaultSatuanBesar = $validated['satuan_besar'] ?? $code->satuan_besar ?? 'LUSIN';
            $defaultNilai = (int) ($validated['nilai_konversi'] ?? $code->nilai_konversi ?? 12);
            if ($defaultSatuanBesar && $defaultNilai > 0) {
                \App\Models\UnitConversion::updateOrCreate(
                    [ 'kode_barang_id' => $code->id, 'unit_turunan' => strtoupper($defaultSatuanBesar) ],
                    [ 'nilai_konversi' => $defaultNilai, 'is_active' => true ]
                );
            }
        }

        return redirect()->route('code.view-code')
            ->with('success', "Successfully updated code!");
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $code = KodeBarang::findOrFail($id);
        $code->delete();

        return redirect()->route('code.view-code')
            ->with('success', "Successfully deleted code!");
    }

    /**
     * Toggle Active/Inactive status for KodeBarang
     */
    public function toggleStatus($id)
    {
        $code = KodeBarang::findOrFail($id);
        $code->status = ($code->status === 'Active') ? 'Inactive' : 'Active';
        $code->save();

        return back()->with('success', 'Status barang berhasil diubah menjadi ' . $code->status);
    }

    public function searchKodeBarang(Request $request)
    {
        $keyword = $request->input('keyword');

        $kodeBarang = KodeBarang::where('kode_barang', 'like', "%{$keyword}%")
            ->orWhere('name', 'like', "%{$keyword}%")
            ->orWhere('attribute', 'like', "%{$keyword}%")
            ->limit(10)
            ->get();

        // Transform data to match frontend expectations
        $transformedData = $kodeBarang->map(function($item) {
            return [
                'kode_barang' => $item->kode_barang,
                'nama_barang' => $item->name, // Map 'name' to 'nama_barang'
                'attribute' => $item->attribute,
                'merek' => $item->merek,
                'ukuran' => $item->ukuran,
                'satuan_dasar' => $item->satuan_dasar,
                'satuan_besar' => $item->satuan_besar,
                'unit_dasar' => $item->unit_dasar
            ];
        });

        return response()->json($transformedData);
    }

    /**
     * Get next available item code for a specific grup barang
     */
    public function getNextItemCode(Request $request)
    {
        $grupBarangName = $request->input('grup_barang_name');
        
        if (!$grupBarangName) {
            return response()->json(['error' => 'Grup barang name is required'], 400);
        }

        // Cari grup barang
        $grupBarang = \App\Models\GrupBarang::where('name', $grupBarangName)->first();
        
        if (!$grupBarang) {
            return response()->json(['error' => 'Grup barang not found'], 404);
        }

        // Cari semua kode barang yang sudah ada untuk grup ini
        $existingKodeBarangs = KodeBarang::where('grup_barang_id', $grupBarang->id)
            ->pluck('kode_barang')
            ->toArray();

        if (empty($existingKodeBarangs)) {
            // Jika belum ada kode barang untuk grup ini, buat yang pertama
            $nextCode = $grupBarangName . ' - 001';
        } else {
            // Cari nomor urut tertinggi yang sudah ada
            $maxNumber = 0;
            $prefix = $grupBarangName;
            
            foreach ($existingKodeBarangs as $existingCode) {
                // Cari nomor urut dengan format "NAMA GRUP - 001"
                if (preg_match('/^' . preg_quote($prefix, '/') . '\s*-\s*(\d+)$/', $existingCode, $matches)) {
                    $number = intval($matches[1]);
                    $maxNumber = max($maxNumber, $number);
                }
            }
            
            // Generate nomor berikutnya
            $nextNumber = $maxNumber + 1;
            $nextCode = $prefix . ' - ' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
        }

        return response()->json([
            'success' => true,
            'next_code' => $nextCode,
            'grup_barang_id' => $grupBarang->id,
            'existing_codes' => $existingKodeBarangs ?? [],
            'next_number' => $nextNumber ?? 1
        ]);
    }
}