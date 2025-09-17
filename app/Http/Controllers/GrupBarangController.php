<?php

namespace App\Http\Controllers;

use App\Models\GrupBarang;
use App\Models\KodeBarang;
use Illuminate\Http\Request;

class GrupBarangController extends Controller
{
    /**
     * Display a listing of categories.
     */
    public function index()
    {
        $categories = GrupBarang::orderBy('created_at', 'desc')->paginate(10);
        return view('master.grup_barang.index', compact('categories'));
    }

    /**
     * Show the form for creating a new category.
     */
    public function create()
    {
        return view('master.grup_barang.create');
    }

    /**
     * Store a newly created category in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:Active,Inactive',
        ]);

        GrupBarang::create($validated);

        return redirect()->route('grup_barang.index')
            ->with('success', 'Grup Barang berhasil dibuat!');
    }

    /**
     * Show the form for editing the specified category.
     */
    public function edit($id)
    {
        $category = GrupBarang::findOrFail($id);
        $allItems = KodeBarang::orderBy('name')->get(['id','kode_barang','name','grup_barang_id']);
        return view('master.grup_barang.edit', compact('category', 'allItems'));
    }

    /**
     * Update the specified category in storage.
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:Active,Inactive',
        ]);

        $category = GrupBarang::findOrFail($id);
        $category->update($validated);

        return redirect()->route('grup_barang.edit', $category->id)
            ->with('success', 'Grup Barang berhasil diperbarui!');
    }

    /**
     * Assign multiple KodeBarang to this group
     */
    public function assignItems(Request $request, $id)
    {
        $category = GrupBarang::findOrFail($id);
        $request->validate([
            'item_ids' => 'nullable|array',
            'item_ids.*' => 'integer|exists:kode_barangs,id'
        ]);

        // Set grup for selected items
        $selectedIds = $request->input('item_ids', []);

        // Remove this group from items not selected
        KodeBarang::where('grup_barang_id', $category->id)
            ->whereNotIn('id', $selectedIds)
            ->update(['grup_barang_id' => null]);

        // Assign this group to selected items
        if (!empty($selectedIds)) {
            KodeBarang::whereIn('id', $selectedIds)->update(['grup_barang_id' => $category->id]);
        }

        return back()->with('success', 'Barang pada grup berhasil diperbarui.');
    }

    /**
     * Remove the specified category from storage.
     */
    public function destroy($id)
    {
        $category = GrupBarang::findOrFail($id);
        $category->delete();

        return redirect()->route('grup_barang.index')
            ->with('success', 'Grup Barang berhasil dihapus!');
    }

    /**
     * Toggle active status
     */
    public function toggleStatus($id)
    {
        $category = GrupBarang::findOrFail($id);
        $category->update([
            'status' => $category->status === 'Active' ? 'Inactive' : 'Active'
        ]);

        $status = $category->status === 'Active' ? 'diaktifkan' : 'dinonaktifkan';
        return redirect()->route('grup_barang.index')
            ->with('success', "Grup Barang berhasil {$status}!");
    }
}
