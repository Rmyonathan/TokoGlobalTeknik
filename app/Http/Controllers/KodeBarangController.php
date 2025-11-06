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
        // Ambil nama merek dari tabel grup_barang yang sudah dibuat
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
        // Provide sample headers to the view - supporting both formats
        $sampleHeaders = [
            'NO.', 'TGL STOK', 'NAMA BRG', 'MERK', 'TYPE/UKURAN', 'QTY', 'SAT', 'HARGA', 'JUMLAH', 'KETERANGAN BRG /(TGL BELI)', 'BY'
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
            'auto_group_merek' => 'boolean', // New parameter to control auto-grouping
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
                $header = array_map(function($h){
                    $h = trim((string)$h);
                    if ($h === '' || in_array(strtolower($h), ['null','nul','-'])) return null;
                    return $h;
                }, $row);
                continue;
            }
            // Normalize row values: treat textual 'null'/'nul' as empty
            $row = array_map(function($v){
                $v = is_string($v) ? trim($v) : $v;
                if (is_string($v) && in_array(strtolower($v), ['null','nul','-'])) return '';
                return $v;
            }, $row);
            $assoc = [];
            foreach ($header as $idx => $col) {
                if ($col === null) continue; // skip 'null' columns
                $assoc[$col] = $row[$idx] ?? null;
            }
            $rows[] = $assoc;
        }
        fclose($handle);

        // Header alias mapping: support alternative header names/order
        $aliasMap = [
            'nama_barang/name' => 'name',
            'nama_barang' => 'name',
            'name' => 'name',
            'NAMA BRG' => 'name',
            'Merek' => 'merek',
            'MERK' => 'merek',
            'merek' => 'merek',
            'Ukuran' => 'ukuran',
            'TYPE/UKURAN' => 'ukuran',
            'ukuran' => 'ukuran',
            'qty' => 'quantity',
            'QTY' => 'quantity',
            'quantity' => 'quantity',
            'harga_satuan_dasar' => 'harga_jual',
            'harga_beli' => 'harga_beli',
            'HARGA' => 'harga_beli',
            'harga_jual' => 'harga_jual',
            'kode_barang' => 'kode_barang',
            'attribute' => 'attribute',
            'unit_dasar' => 'unit_dasar',
            'SAT' => 'unit_dasar',
            'satuan_dasar' => 'satuan_dasar',
            'satuan_besar' => 'satuan_besar',
            'nilai_konversi' => 'nilai_konversi',
            'ongkos_kuli_default' => 'ongkos_kuli_default',
            'KETERANGAN BRG /(TGL BELI)' => 'keterangan',
            'keterangan' => 'keterangan',
            'KETERANGAN' => 'keterangan',
            'BY' => 'input_by',
            'input_by' => 'input_by',
            'INPUT_BY' => 'input_by',
            // Ignore columns that we don't need
            'NO.' => null,
            'TGL STOK' => null,
            'JUMLAH' => null,
        ];

        // Normalize each row keys via alias map
        $normalizedRows = [];
        foreach ($rows as $row) {
            $norm = [];
            foreach ($row as $k => $v) {
                $key = $aliasMap[$k] ?? $aliasMap[strtolower($k)] ?? null;
                if ($key) {
                    $norm[$key] = $v;
                }
            }
            $normalizedRows[] = $norm;
        }

        $created = 0; $updated = 0; $errors = [];
        $parseNumber = function($value) {
            if ($value === null || $value === '') return 0;
            
            // Check if it's a simple numeric value (no thousand separators)
            if (is_numeric($value) && !preg_match('/[,\.]/', (string)$value)) {
                return (float)$value;
            }
            
            $s = (string)$value;
            $s = str_replace(["\xC2\xA0", ' '], '', $s); // remove nbsp and spaces
            
            // Remove quotes if present
            $s = trim($s, '"\'');
            
            // Handle Indonesian number format: "79,000" -> 79000
            // Pattern: digits with commas as thousand separators (no decimal points)
            if (preg_match('/^(\d{1,3}(?:,\d{3})*)$/', $s)) {
                $s = str_replace(',', '', $s);
                return (float)$s;
            }
            
            // Handle format with dots as thousand separators: "79.000" -> 79000
            // Pattern: digits with dots as thousand separators (no decimal points)
            if (preg_match('/^(\d{1,3}(?:\.\d{3})*)$/', $s)) {
                $s = str_replace('.', '', $s);
                return (float)$s;
            }
            
            // Handle mixed format: "1,500,000" or "1.500.000" -> 1500000
            $s = preg_replace('/[^0-9,\.\-]/', '', $s);
            
            // If there are only commas or only dots (not mixed), treat as thousand separators
            $hasComma = strpos($s, ',') !== false;
            $hasDot = strpos($s, '.') !== false;
            
            if ($hasComma && !$hasDot) {
                // Only commas - treat as thousand separators
                $s = str_replace(',', '', $s);
            } elseif ($hasDot && !$hasComma) {
                // Only dots - could be thousand separators or decimal
                // If the dot is followed by exactly 3 digits, treat as thousand separator
                if (preg_match('/^(\d{1,3}(?:\.\d{3})*)$/', $s)) {
                    $s = str_replace('.', '', $s);
                }
                // Otherwise, leave it as decimal
            }
            
            if ($s === '' || $s === '-') return 0;
            return (float)$s;
        };
        foreach ($normalizedRows as $index => $data) {
            try {
                // Skip completely empty rows (all values empty after trim)
                $hasAny = false;
                foreach ($data as $vv) { if (trim((string)$vv) !== '') { $hasAny = true; break; } }
                if (!$hasAny) { continue; }

                $kode = trim((string)($data['kode_barang'] ?? ''));
                $name = trim((string)($data['name'] ?? ''));
                $merek = trim((string)($data['merek'] ?? ''));
                
                if ($kode === '') {
                    // Generate kode based on merek if available
                    if (!empty($merek)) {
                        // Clean merek for kode (remove spaces, special chars, convert to uppercase)
                        $merekClean = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $merek));
                        $merekPrefix = substr($merekClean, 0, 3); // Take first 3 characters
                        
                        // Find next number for this merek
                        $lastKode = KodeBarang::where('kode_barang', 'like', $merekPrefix . '%')
                            ->orderBy('kode_barang', 'desc')
                            ->first();
                        
                        if ($lastKode) {
                            // Extract number from last kode and increment
                            if (preg_match('/' . preg_quote($merekPrefix, '/') . '(\d+)$/', $lastKode->kode_barang, $matches)) {
                                $nextNumber = (int)$matches[1] + 1;
                            } else {
                                $nextNumber = 1;
                            }
                        } else {
                            $nextNumber = 1;
                        }
                        
                        $kode = $merekPrefix . str_pad((string)$nextNumber, 3, '0', STR_PAD_LEFT);
                    } else {
                        // Fallback to AUTO format if no merek
                        $kode = 'AUTO-'.date('Ymd').'-'.str_pad((string)($index+1), 3, '0', STR_PAD_LEFT);
                    }
                }
                if ($name === '') {
                    // Fallback: build name from merek/ukuran/keterangan
                    $nameCandidates = [
                        trim((string)($data['merek'] ?? '')),
                        trim((string)($data['ukuran'] ?? '')),
                        trim((string)($data['keterangan'] ?? '')),
                    ];
                    $name = trim(implode(' ', array_filter($nameCandidates)));
                    if ($name === '' && $kode !== '') {
                        // Last fallback: use kode as name to allow importing
                        $name = $kode;
                    }
                    if ($name === '') {
                        $errors[] = 'Baris ' . ($index + 2) . ': dilewati karena kolom nama kosong';
                        continue;
                    }
                }

                // Ensure attribute is not null (DB constraint). Default to 'GENERAL' if missing
                $attribute = trim((string)($data['attribute'] ?? ''));
                if ($attribute === '') {
                    $attribute = 'GENERAL';
                }

                // Handle quantity and pricing
                $quantity = isset($data['quantity']) && $data['quantity'] !== '' ? (float) $data['quantity'] : 0;
                $hargaBeli = isset($data['harga_beli']) && $data['harga_beli'] !== '' ? $parseNumber($data['harga_beli']) : 0;
                
                // Calculate harga jual with 35% margin if not provided or empty
                $hargaJual = isset($data['harga_jual']) && $data['harga_jual'] !== '' ? $parseNumber($data['harga_jual']) : 0;
                if ($hargaJual == 0 && $hargaBeli > 0) {
                    $hargaJual = $hargaBeli * 1.35; // 35% margin
                }

                $payload = [
                    'kode_barang' => $kode,
                    'name' => $name,
                    'attribute' => $attribute,
                    'merek' => $data['merek'] ?? null,
                    'ukuran' => $data['ukuran'] ?? null,
                    'unit_dasar' => $data['unit_dasar'] ?? 'PCS',
                    'satuan_dasar' => $data['satuan_dasar'] ?? null,
                    'satuan_besar' => $data['satuan_besar'] ?? null,
                    'nilai_konversi' => isset($data['nilai_konversi']) && $data['nilai_konversi'] !== '' ? (int) $data['nilai_konversi'] : null,
                    'harga_jual' => $hargaJual,
                    'ongkos_kuli_default' => isset($data['ongkos_kuli_default']) && $data['ongkos_kuli_default'] !== '' ? (float) $data['ongkos_kuli_default'] : 0,
                    'status' => 'Active',
                    'cost' => $hargaBeli, // Set cost as harga_beli
                    'keterangan' => $data['keterangan'] ?? null,
                    'input_by' => $data['input_by'] ?? null,
                ];

                // Map merek automatically by attribute if auto-grouping is enabled
                $autoGroupMerek = $request->input('auto_group_merek', true); // Default to true for backward compatibility
                
                if ($autoGroupMerek) {
                    // Option 1: Use merek field to create/assign grup barang
                    if (!empty($payload['merek'])) {
                        // Try to find existing grup with same name as merek
                        $grup = \App\Models\GrupBarang::where('name', $payload['merek'])->first();
                        
                        if (!$grup) {
                            // Create new grup barang if it doesn't exist
                            $grup = \App\Models\GrupBarang::create([
                                'name' => $payload['merek'],
                                'description' => 'Auto-created from import',
                                'status' => 'Active'
                            ]);
                        }
                        
                        if ($grup) {
                            $payload['grup_barang_id'] = $grup->id;
                            $payload['attribute'] = $grup->name; // Sync attribute with grup name
                        }
                    } elseif (!empty($payload['attribute'])) {
                        // Fallback: Use attribute field
                        $grup = \App\Models\GrupBarang::where('name', $payload['attribute'])->first();
                        if ($grup) {
                            $payload['grup_barang_id'] = $grup->id;
                        }
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

                // Handle stock quantity if provided
                if ($quantity > 0) {
                    try {
                        // Update or create stock record
                        \App\Models\Stock::updateOrCreate(
                            [ 'kode_barang' => $kode ],
                            [
                                'nama_barang' => $name,
                                'good_stock' => $quantity,
                                'bad_stock' => 0,
                                'so' => 0,
                                'satuan' => $payload['unit_dasar']
                            ]
                        );
                    } catch (\Exception $e) {
                        $errors[] = 'Baris ' . ($index + 2) . ': Gagal menyimpan stock - ' . $e->getMessage();
                    }
                }
            } catch (\Throwable $e) {
                $errors[] = 'Baris ' . ($index + 2) . ': ' . $e->getMessage();
            }
        }

        if (!empty($errors)) {
            return back()->with('warning', "Selesai dengan peringatan. Created: {$created}, Updated: {$updated}")
                ->withErrors(['detail' => implode("\n", $errors)]);
        }

        return redirect()->route('master.barang')
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
            'cost' => 'nullable|numeric|min:0',
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
        
        // Handle cost and pricing
        $validated['cost'] = $validated['cost'] ?? 0;
        
        // Calculate harga jual with 30% margin if not provided or empty
        $hargaJual = $validated['harga_jual'] ?? 0;
        if ($hargaJual == 0 && $validated['cost'] > 0) {
            $hargaJual = $validated['cost'] * 1.30; // 30% margin
        }
        $validated['harga_jual'] = $hargaJual;
        
        // Set default values if not provided
        $validated['price'] = $validated['price'] ?? 0;
        $validated['ongkos_kuli_default'] = $validated['ongkos_kuli_default'] ?? 0;

        // Cari grup barang berdasarkan attribute yang dipilih
        $grupBarang = \App\Models\GrupBarang::where('name', $validated['attribute'])->first();
        if ($grupBarang) {
            $validated['grup_barang_id'] = $grupBarang->id;
        }

        // Create the new KodeBarang
        $kodeBarang = KodeBarang::create($validated);

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
            'name' => 'required|string|max:255',
            'grup_barang_id' => 'required|string|max:255',
            'attribute' => 'required|string|max:255',
            'merek' => 'nullable|string|max:255',
            'ukuran' => 'nullable|string|max:100',
            'kode_barang' => 'required|string|max:255',
            'unit_dasar' => 'required|string|max:20',
            'harga_jual' => 'required|numeric|min:0',
            'ongkos_kuli_default' => 'nullable|numeric|min:0',
            'cost' => 'nullable|numeric|min:0',
            'satuan_besar' => 'nullable|string|max:50',
            'satuan_dasar' => 'nullable|string|max:50',
            'nilai_konversi' => 'nullable|integer|min:1',
            'min_stock' => 'nullable|integer|min:0',
            'keterangan' => 'nullable|string|max:500',
            'input_by' => 'nullable|string|max:100',
        ], [
            'name.required' => 'Nama barang harus diisi',
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
            'cost.numeric' => 'Harga beli harus berupa angka',
            'cost.min' => 'Harga beli minimal 0',
            'keterangan.max' => 'Keterangan maksimal 500 karakter',
            'input_by.max' => 'Input By maksimal 100 karakter',
        ]);

        $code = KodeBarang::findOrFail($id);
        
        // Map grup barang name to its ID
        $grupBarang = \App\Models\GrupBarang::where('name', $validated['grup_barang_id'])->first();
        if ($grupBarang) {
            $validated['grup_barang_id'] = $grupBarang->id;
        } else {
            unset($validated['grup_barang_id']);
        }
        
        // Keep attribute synced to selected group name (from form)
        $validated['attribute'] = $request->input('grup_barang_id', $code->attribute);
        
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
            // Get global stock for this product
            $globalStock = \App\Models\Stock::getGlobalStock($item->kode_barang);
            
            return [
                'id' => $item->id,
                'kode_barang' => $item->kode_barang,
                'name' => $item->name, // Keep 'name' for frontend compatibility
                'attribute' => $item->attribute,
                'merek' => $item->merek,
                'ukuran' => $item->ukuran,
                'cost' => $item->cost,
                'length' => $item->length,
                'satuan_dasar' => $item->satuan_dasar,
                'satuan_besar' => $item->satuan_besar,
                'unit_dasar' => $item->unit_dasar,
                'remaining_stock' => $globalStock->good_stock,
                'stock_unit' => $globalStock->satuan
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
                if (preg_match('/^' . preg_quote($prefix, '/') . '\\s*-\\s*(\\d+)$/', $existingCode, $matches)) {
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