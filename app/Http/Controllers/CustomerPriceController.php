<?php

namespace App\Http\Controllers;

use App\Models\CustomerPrice;
use App\Models\Customer;
use App\Models\KodeBarang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CustomerPriceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = CustomerPrice::with(['customer', 'kodeBarang']);

        // Filter by customer
        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        // Filter by is_active
        if ($request->filled('is_active')) {
            $query->where('is_active', (int) $request->is_active === 1);
        }

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('customer', function($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('alamat', 'like', "%{$search}%");
            })->orWhereHas('kodeBarang', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('kode_barang', 'like', "%{$search}%");
            });
        }

        $customerPrices = $query->orderBy('created_at', 'desc')->paginate(15);
        $customers = Customer::where('is_active', 1)->orderBy('nama')->get();

        return view('customer_price.index', compact('customerPrices', 'customers'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $customers = Customer::where('is_active', 1)->orderBy('nama')->get();
        $kodeBarangs = KodeBarang::where('status', 'Active')->orderBy('name')->get();

        return view('customer_price.create', compact('customers', 'kodeBarangs'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'kode_barang_id' => 'required|exists:kode_barangs,id',
            'harga_jual_khusus' => 'required|numeric|min:0',
            'is_active' => 'required|in:0,1',
            'keterangan' => 'nullable|string|max:500',
        ], [
            'customer_id.required' => 'Pelanggan harus dipilih',
            'customer_id.exists' => 'Pelanggan tidak ditemukan',
            'kode_barang_id.required' => 'Barang harus dipilih',
            'kode_barang_id.exists' => 'Barang tidak ditemukan',
            'harga_jual_khusus.required' => 'Harga khusus harus diisi',
            'harga_jual_khusus.numeric' => 'Harga khusus harus berupa angka',
            'harga_jual_khusus.min' => 'Harga khusus minimal 0',
            'is_active.required' => 'Status harus dipilih',
            'is_active.in' => 'Status tidak valid',
            'keterangan.max' => 'Keterangan maksimal 500 karakter',
        ]);

        // Check if customer price already exists for this customer and item
        $existingPrice = CustomerPrice::where('customer_id', $validated['customer_id'])
            ->where('kode_barang_id', $validated['kode_barang_id'])
            ->where('is_active', true)
            ->first();

        if ($existingPrice) {
            return back()->withErrors([
                'kode_barang_id' => 'Harga khusus untuk pelanggan dan barang ini sudah ada. Silakan edit yang sudah ada atau nonaktifkan terlebih dahulu.'
            ])->withInput();
        }

        try {
            DB::beginTransaction();

            $customerPrice = CustomerPrice::create($validated);

            Log::info('Customer price created', [
                'id' => $customerPrice->id,
                'customer_id' => $customerPrice->customer_id,
                'kode_barang_id' => $customerPrice->kode_barang_id,
                'harga_jual_khusus' => $customerPrice->harga_jual_khusus,
            ]);

            DB::commit();

            return redirect()->route('customer-price.index')
                ->with('success', 'Harga khusus pelanggan berhasil ditambahkan');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create customer price', [
                'error' => $e->getMessage(),
                'data' => $validated
            ]);

            return back()->withErrors(['error' => 'Terjadi kesalahan saat menyimpan data'])
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(CustomerPrice $customerPrice)
    {
        $customerPrice->load(['customer', 'kodeBarang']);
        return view('customer_price.show', compact('customerPrice'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(CustomerPrice $customerPrice)
    {
        $customers = Customer::where('is_active', 1)->orderBy('nama')->get();
        $kodeBarangs = KodeBarang::where('status', 'Active')->orderBy('name')->get();

        return view('customer_price.edit', compact('customerPrice', 'customers', 'kodeBarangs'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, CustomerPrice $customerPrice)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'kode_barang_id' => 'required|exists:kode_barangs,id',
            'harga_jual_khusus' => 'required|numeric|min:0',
            'is_active' => 'required|in:0,1',
            'keterangan' => 'nullable|string|max:500',
        ], [
            'customer_id.required' => 'Pelanggan harus dipilih',
            'customer_id.exists' => 'Pelanggan tidak ditemukan',
            'kode_barang_id.required' => 'Barang harus dipilih',
            'kode_barang_id.exists' => 'Barang tidak ditemukan',
            'harga_jual_khusus.required' => 'Harga khusus harus diisi',
            'harga_jual_khusus.numeric' => 'Harga khusus harus berupa angka',
            'harga_jual_khusus.min' => 'Harga khusus minimal 0',
            'is_active.required' => 'Status harus dipilih',
            'is_active.in' => 'Status tidak valid',
            'keterangan.max' => 'Keterangan maksimal 500 karakter',
        ]);

        // Check if customer price already exists for this customer and item (excluding current record)
        $existingPrice = CustomerPrice::where('customer_id', $validated['customer_id'])
            ->where('kode_barang_id', $validated['kode_barang_id'])
            ->where('is_active', true)
            ->where('id', '!=', $customerPrice->id)
            ->first();

        if ($existingPrice) {
            return back()->withErrors([
                'kode_barang_id' => 'Harga khusus untuk pelanggan dan barang ini sudah ada. Silakan edit yang sudah ada atau nonaktifkan terlebih dahulu.'
            ])->withInput();
        }

        try {
            DB::beginTransaction();

            $customerPrice->update($validated);

            Log::info('Customer price updated', [
                'id' => $customerPrice->id,
                'customer_id' => $customerPrice->customer_id,
                'kode_barang_id' => $customerPrice->kode_barang_id,
                'harga_jual_khusus' => $customerPrice->harga_jual_khusus,
            ]);

            DB::commit();

            return redirect()->route('customer-price.index')
                ->with('success', 'Harga khusus pelanggan berhasil diperbarui');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update customer price', [
                'id' => $customerPrice->id,
                'error' => $e->getMessage(),
                'data' => $validated
            ]);

            return back()->withErrors(['error' => 'Terjadi kesalahan saat memperbarui data'])
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CustomerPrice $customerPrice)
    {
        try {
            DB::beginTransaction();

            Log::info('Customer price deleted', [
                'id' => $customerPrice->id,
                'customer_id' => $customerPrice->customer_id,
                'kode_barang_id' => $customerPrice->kode_barang_id,
            ]);

            $customerPrice->delete();

            DB::commit();

            return redirect()->route('customer-price.index')
                ->with('success', 'Harga khusus pelanggan berhasil dihapus');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to delete customer price', [
                'id' => $customerPrice->id,
                'error' => $e->getMessage()
            ]);

            return back()->withErrors(['error' => 'Terjadi kesalahan saat menghapus data']);
        }
    }

    /**
     * Toggle status of customer price
     */
    public function toggleStatus(CustomerPrice $customerPrice)
    {
        try {
            $customerPrice->is_active = !$customerPrice->is_active;
            $customerPrice->save();

            $statusText = $customerPrice->is_active ? 'diaktifkan' : 'dinonaktifkan';

            return back()->with('success', "Harga khusus pelanggan berhasil {$statusText}");

        } catch (\Exception $e) {
            Log::error('Failed to toggle customer price status', [
                'id' => $customerPrice->id,
                'error' => $e->getMessage()
            ]);

            return back()->withErrors(['error' => 'Terjadi kesalahan saat mengubah status']);
        }
    }

    /**
     * Get customer price for specific customer and item
     */
    public function getCustomerPrice(Request $request)
    {
        $customerId = $request->input('customer_id');
        $kodeBarangId = $request->input('kode_barang_id');

        if (!$customerId || !$kodeBarangId) {
            return response()->json(['error' => 'Customer ID and Kode Barang ID are required'], 400);
        }

        $customerPrice = CustomerPrice::where('customer_id', $customerId)
            ->where('kode_barang_id', $kodeBarangId)
            ->where('is_active', true)
            ->first();

        if ($customerPrice) {
            return response()->json([
                'success' => true,
                'customer_price' => $customerPrice,
                'harga_khusus' => $customerPrice->harga_jual_khusus,
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Tidak ada harga khusus untuk pelanggan dan barang ini'
        ]);
    }
}
