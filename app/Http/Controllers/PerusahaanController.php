<?php
namespace App\Http\Controllers;

use App\Models\Perusahaan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PerusahaanController extends Controller
{
    public function index()
    {
        $perusahaan = Perusahaan::all();
        return view('master.perusahaan.index', compact('perusahaan'));
    }

    public function create()
    {
        return view('master.perusahaan.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'alamat' => 'required|string',
            'kota' => 'nullable|string|max:255',
            'kode_pos' => 'nullable|string|max:10',
            'telepon' => 'nullable|string|max:20',
            'fax' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'website' => 'nullable|url|max:255',
            'npwp' => 'nullable|string|max:50',
            'catatan_nota' => 'nullable|string',
            'catatan_surat_jalan' => 'nullable|string',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'is_active' => 'boolean'
        ]);

        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('public/logos');
            $validated['logo'] = Storage::url($path);
        }

        Perusahaan::create($validated);

        return redirect()
            ->route('master.perusahaan.index')
            ->with('success', 'Data perusahaan berhasil ditambahkan');
    }

    public function edit($id)
    {
        $perusahaan = Perusahaan::findOrFail($id);
        return view('master.perusahaan.edit', compact('perusahaan'));
    }

    public function update(Request $request, $id)
    {
        $perusahaan = Perusahaan::findOrFail($id);
        
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'alamat' => 'required|string',
            'kota' => 'nullable|string|max:255',
            'kode_pos' => 'nullable|string|max:10',
            'telepon' => 'nullable|string|max:20',
            'fax' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'website' => 'nullable|url|max:255',
            'npwp' => 'nullable|string|max:50',
            'catatan_nota' => 'nullable|string',
            'catatan_surat_jalan' => 'nullable|string',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'is_active' => 'boolean'
        ]);

        if ($request->hasFile('logo')) {
            // Delete old logo if exists
            if ($perusahaan->logo) {
                Storage::delete(str_replace('/storage', 'public', $perusahaan->logo));
            }
            $path = $request->file('logo')->store('public/logos');
            $validated['logo'] = Storage::url($path);
        }

        $perusahaan->update($validated);

        return redirect()
            ->route('master.perusahaan.index')
            ->with('success', 'Data perusahaan berhasil diperbarui');
    }

    public function destroy($id)
    {
        $perusahaan = Perusahaan::findOrFail($id);
        
        // Delete logo if exists
        if ($perusahaan->logo) {
            Storage::delete(str_replace('/storage', 'public', $perusahaan->logo));
        }
        
        $perusahaan->delete();

        return redirect()
            ->route('master.perusahaan.index')
            ->with('success', 'Data perusahaan berhasil dihapus');
    }
}