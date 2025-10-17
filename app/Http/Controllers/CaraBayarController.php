<?php

namespace App\Http\Controllers;

use App\Models\CaraBayar;
use App\Services\AccountingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CaraBayarController extends Controller
{
    protected $accountingService;

    public function __construct(AccountingService $accountingService)
    {
        $this->accountingService = $accountingService;
    }

    public function index()
    {
        $cara_bayar = CaraBayar::with('coaAccount')->get();
        return view('master.cara_bayar', compact('cara_bayar'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'metode' => 'required|in:Tunai,Non Tunai',
            'nama' => 'required|string|max:255',
        ]);

        try {
            DB::beginTransaction();

            // Create CaraBayar
            $caraBayar = CaraBayar::create($request->only('metode', 'nama'));

            // Automatically create or link COA account
            $coaAccount = $this->accountingService->createOrLinkCoaAccountForCaraBayar($caraBayar);
            
            if ($coaAccount) {
                $caraBayar->update(['coa_account_id' => $coaAccount->id]);
                
                Log::info('CaraBayar successfully linked to COA account', [
                    'cara_bayar_id' => $caraBayar->id,
                    'coa_account_id' => $coaAccount->id,
                    'coa_code' => $coaAccount->code,
                    'coa_name' => $coaAccount->name
                ]);
            } else {
                Log::warning('Failed to create or link COA account for CaraBayar', [
                    'cara_bayar_id' => $caraBayar->id,
                    'metode' => $caraBayar->metode,
                    'nama' => $caraBayar->nama
                ]);
            }

            DB::commit();

            return redirect()->back()->with('success', 'Cara Bayar berhasil ditambahkan' . 
                ($coaAccount ? ' dan terhubung dengan akun COA.' : ' (COA account tidak dapat dibuat).'));

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Failed to create CaraBayar with COA integration', [
                'error' => $e->getMessage(),
                'request_data' => $request->all()
            ]);

            return redirect()->back()
                ->with('error', 'Gagal menambahkan Cara Bayar: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function destroy($id)
    {
        try {
            $caraBayar = CaraBayar::findOrFail($id);
            
            // Log the deletion for audit purposes
            Log::info('CaraBayar deleted', [
                'cara_bayar_id' => $caraBayar->id,
                'metode' => $caraBayar->metode,
                'nama' => $caraBayar->nama,
                'coa_account_id' => $caraBayar->coa_account_id
            ]);
            
            $caraBayar->delete();
            
            return redirect()->back()->with('success', 'Cara Bayar berhasil dihapus.');
            
        } catch (\Exception $e) {
            Log::error('Failed to delete CaraBayar', [
                'cara_bayar_id' => $id,
                'error' => $e->getMessage()
            ]);
            
            return redirect()->back()->with('error', 'Gagal menghapus Cara Bayar: ' . $e->getMessage());
        }
    }

    /**
     * Manually link existing CaraBayar to COA account
     */
    public function linkToCoa($id)
    {
        try {
            $caraBayar = CaraBayar::findOrFail($id);
            
            if ($caraBayar->hasCoaAccount()) {
                return redirect()->back()->with('warning', 'Cara Bayar ini sudah terhubung dengan akun COA.');
            }

            $coaAccount = $this->accountingService->createOrLinkCoaAccountForCaraBayar($caraBayar);
            
            if ($coaAccount) {
                $caraBayar->update(['coa_account_id' => $coaAccount->id]);
                
                Log::info('CaraBayar manually linked to COA account', [
                    'cara_bayar_id' => $caraBayar->id,
                    'coa_account_id' => $coaAccount->id,
                    'coa_code' => $coaAccount->code
                ]);
                
                return redirect()->back()->with('success', 'Cara Bayar berhasil terhubung dengan akun COA.');
            } else {
                return redirect()->back()->with('error', 'Gagal menghubungkan dengan akun COA. Periksa konfigurasi COA.');
            }
            
        } catch (\Exception $e) {
            Log::error('Failed to manually link CaraBayar to COA', [
                'cara_bayar_id' => $id,
                'error' => $e->getMessage()
            ]);
            
            return redirect()->back()->with('error', 'Gagal menghubungkan dengan akun COA: ' . $e->getMessage());
        }
    }
}
