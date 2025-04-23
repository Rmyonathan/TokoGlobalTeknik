<?php

namespace App\Http\Controllers;

use App\Models\StokOwner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StokOwnerController extends Controller
{
    public function index()
    {
        $stokOwners = StokOwner::all();
        return view('master.stok_owner', compact('stokOwners'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode_stok_owner' => 'required|unique:stok_owners,kode_stok_owner|max:12',
            'keterangan' => 'required',
            'default' => 'nullable|boolean',
        ]);
        // Set default ke `true` jika checkbox dicentang, atau `false` jika tidak dicentang
        $validated['default'] = $request->has('default');

        // Simpan data ke database
        StokOwner::create($validated);

        return redirect()->route('stok_owner.index')->with('success', 'Stok Owner added successfully.');
    }

    public function destroy(StokOwner $stokOwner)
    {
        $stokOwner->delete();
        return redirect()->route('stok_owner.index')->with('success', 'Stok Owner deleted successfully.');
    }
    
    public function search(Request $request)
    {
        $keyword = $request->get('keyword');
        $sales = DB::table('stok_owners')
            ->where('keterangan', 'like', "%{$keyword}%")
            ->orWhere('kode_stok_owner', 'like', "%{$keyword}%")
            ->limit(10)
            ->get();

        return response()->json($sales);
    }
}