<?php

namespace App\Http\Controllers;

use App\Models\Wilayah;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class WilayahController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $wilayahs = Wilayah::orderBy('nama_wilayah')->paginate(10);
        return view('wilayah.index', compact('wilayahs'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('wilayah.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama_wilayah' => 'required|string|max:100|unique:wilayahs,nama_wilayah',
            'keterangan' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Fix checkbox handling - check if the field exists in request
        $isActive = $request->has('is_active') ? true : false;

        Wilayah::create([
            'nama_wilayah' => $request->nama_wilayah,
            'keterangan' => $request->keterangan,
            'is_active' => $isActive
        ]);

        return redirect()->route('wilayah.index')
            ->with('success', 'Wilayah berhasil ditambahkan!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Wilayah $wilayah)
    {
        return view('wilayah.show', compact('wilayah'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Wilayah $wilayah)
    {
        return view('wilayah.edit', compact('wilayah'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Wilayah $wilayah)
    {
        $validator = Validator::make($request->all(), [
            'nama_wilayah' => 'required|string|max:100|unique:wilayahs,nama_wilayah,' . $wilayah->id,
            'keterangan' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Fix checkbox handling - check if the field exists in request
        $isActive = $request->has('is_active') ? true : false;

        $wilayah->update([
            'nama_wilayah' => $request->nama_wilayah,
            'keterangan' => $request->keterangan,
            'is_active' => $isActive
        ]);

        return redirect()->route('wilayah.index')
            ->with('success', 'Wilayah berhasil diperbarui!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Wilayah $wilayah)
    {
        // Check if wilayah has customers
        if ($wilayah->customers()->count() > 0) {
            return redirect()->route('wilayah.index')
                ->with('error', 'Wilayah tidak dapat dihapus karena masih memiliki pelanggan!');
        }

        $wilayah->delete();

        return redirect()->route('wilayah.index')
            ->with('success', 'Wilayah berhasil dihapus!');
    }

    /**
     * Toggle active status
     */
    public function toggleStatus(Wilayah $wilayah)
    {
        $wilayah->update([
            'is_active' => !$wilayah->is_active
        ]);

        $status = $wilayah->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return redirect()->route('wilayah.index')
            ->with('success', "Wilayah berhasil {$status}!");
    }
}
