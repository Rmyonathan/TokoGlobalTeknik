<?php

namespace App\Http\Controllers;

use App\Models\CaraBayar;
use Illuminate\Http\Request;

class CaraBayarController extends Controller
{
    public function index()
    {
        $cara_bayar = CaraBayar::all();
        return view('master.cara_bayar', compact('cara_bayar'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'metode' => 'required|in:Tunai,Non Tunai',
            'nama' => 'required|string|max:255',
        ]);

        CaraBayar::create($request->only('metode', 'nama'));

        return redirect()->back()->with('success', 'Cara Bayar berhasil ditambahkan.');
    }

    public function destroy($id)
    {
        CaraBayar::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Cara Bayar berhasil dihapus.');
    }
}
