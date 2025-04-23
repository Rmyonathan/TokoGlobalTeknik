<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomerController extends Controller
{
    public function index()
    {
        $lastCustomer = Customer::orderBy('id', 'desc')->first();
        $newKodeCustomer = $lastCustomer ? str_pad($lastCustomer->id + 1, 4, '0', STR_PAD_LEFT) : '0001';

        $customers = Customer::all();
        return view('master.customers', compact('customers', 'newKodeCustomer'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required',
            'alamat' => 'required',
            'hp' => 'required',
            'telepon' => 'nullable',
        ]);
        // Generate kode_customer otomatis
        $lastCustomer = Customer::orderBy('id', 'desc')->first();
        $newKodeCustomer = $lastCustomer ? str_pad($lastCustomer->id + 1, 4, '0', STR_PAD_LEFT) : '0001';

        // Tambahkan kode_customer ke data yang akan disimpan
        $validated['kode_customer'] = $newKodeCustomer;

        Customer::create($validated);

        return redirect()->route('customers.index')->with('success', 'Customer added successfully.');
    }

    public function getCustomers(Request $request)
    {
        $keyword = $request->keyword;

        $customers = Customer::where('nama', 'like', "%{$keyword}%")
            ->orWhere('kode_customer', 'like', "%{$keyword}%")
            ->limit(10)
            ->get();

        return response()->json($customers);
    }

    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'kode_customer' => 'required|unique:customers,kode_customer,' . $customer->id,
            'nama' => 'required',
            'alamat' => 'required',
            'hp' => 'required',
            'telepon' => 'nullable',
        ]);

        $customer->update($validated);
        return redirect()->route('customers.index')->with('success', 'Customer updated successfully.');
    }

    public function destroy(Customer $customer)
    {
        $customer->delete();
        return redirect()->route('customers.index')->with('success', 'Customer deleted successfully.');
    }
    
    public function search(Request $request)
    {
        $keyword = $request->get('keyword');
        $customers = DB::table('customers')
            ->where('nama', 'like', "%{$keyword}%")
            ->orWhere('kode_customer', 'like', "%{$keyword}%")
            ->limit(10)
            ->get();

        return response()->json($customers);
    }
}