<?php

namespace App\Http\Controllers;

use App\Models\KategoriBarang;
use Illuminate\Http\Request;

class KategoriBarangController extends Controller
{
    /**
     * Display a listing of categories.
     */
    public function index()
    {
        $categories = KategoriBarang::orderBy('created_at', 'desc')->paginate(10);
        return view('master.kategori.index', compact('categories'));
    }

    /**
     * Show the form for creating a new category.
     */
    public function create()
    {
        return view('master.kategori.create');
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

        KategoriBarang::create($validated);

        return redirect()->route('kategori.index')
            ->with('success', 'Category created successfully!');
    }

    /**
     * Show the form for editing the specified category.
     */
    public function edit($id)
    {
        $category = KategoriBarang::findOrFail($id);
        return view('master.kategori.edit', compact('category'));
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

        $category = KategoriBarang::findOrFail($id);
        $category->update($validated);

        return redirect()->route('master.kategori.index')
            ->with('success', 'Category updated successfully!');
    }

    /**
     * Remove the specified category from storage.
     */
    public function destroy($id)
    {
        $category = KategoriBarang::findOrFail($id);
        $category->delete();

        return redirect()->route('kategori.index')
            ->with('success', 'Category deleted successfully!');
    }
}