<?php

namespace App\Http\Controllers;

use App\Models\KodeBarang;
use App\Http\Requests\StoreKodeBarangRequest;
use App\Http\Requests\UpdateKodeBarangRequest;
use Illuminate\Http\Request;

class KodeBarangController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function createCode()
    {
        //
        return view('panels.add-code');
    }

    public function viewCode()
    {
        //
        $codes = KodeBarang::paginate(10);

        return view('panels.view-code', compact('codes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function storeCode(Request $request)
    {
        // Validate the request
        $validated = $request->validate([
            'attribute' => 'required|string|max:255',
            'kode_barang' => 'required|string|max:255',
            'length' => 'required|numeric|min:0.1',
        ], [
            'kode_barang.required' => 'Item code is required',
            'kode_barang.string' => 'Item code must be a valid string',
            'kode_barang.max' => 'Item code may not be greater than 255 characters',

            'attribute.required' => 'Panel name is required',
            'attribute.string' => 'Panel name must be a valid string',
            'attribute.max' => 'Panel name may not be greater than 255 characters',

            'length.required' => 'Panel length is required',
            'length.numeric' => 'Panel length must be a number',
            'length.min' => 'Panel length must be at least 0.1 meters',
        ]);

        // $attribute = $validated['attribute'];
        // $length = $validated['length'];
        // $kode_barang = $validated['kode_barang'];

        KodeBarang::create($validated);

        return redirect()->route('code.view-code')
            ->with('success', "Successfully add group code!");
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
    public function edit(KodeBarang $kodeBarang)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateKodeBarangRequest $request, KodeBarang $kodeBarang)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(KodeBarang $kodeBarang)
    {
        //
    }
}
