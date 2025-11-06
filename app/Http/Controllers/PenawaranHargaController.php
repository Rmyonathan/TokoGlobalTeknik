<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\KodeBarang;
use App\Models\Perusahaan;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PenawaranHargaController extends Controller
{
    /**
     * Display catalog/price list page with multi-select
     */
    public function index(Request $request)
    {
        // Get search parameter
        $search = $request->input('search');
        
        // Query barang with pagination
        $query = KodeBarang::orderBy('kode_barang', 'asc');
        
        // Apply search filter if exists
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('kode_barang', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%")
                  ->orWhere('merek', 'like', "%{$search}%")
                  ->orWhere('ukuran', 'like', "%{$search}%");
            });
        }
        
        // Paginate results (50 items per page)
        $barangs = $query->paginate(50)->withQueryString();

        // Add stock info to each barang
        $barangs->getCollection()->transform(function ($barang) {
            $totalStock = DB::table('stocks')
                ->where('kode_barang', $barang->kode_barang)
                ->sum('good_stock');
            
            $barang->total_stock = $totalStock ?? 0;
            return $barang;
        });

        // Get company info for header
        $perusahaan = Perusahaan::first();

        return view('penawaran.index', compact('barangs', 'perusahaan'));
    }

    /**
     * Export selected items to PDF
     */
    public function exportPdf(Request $request)
    {
        try {
            $selectedItemsJson = $request->input('selected_items', '[]');
            $selectedIds = json_decode($selectedItemsJson, true);
            
            if (empty($selectedIds) || !is_array($selectedIds)) {
                return back()->with('error', 'Pilih minimal 1 barang untuk di-export');
            }

            // Get selected items
            $barangs = KodeBarang::whereIn('id', $selectedIds)
                ->orderBy('kode_barang', 'asc')
                ->get();

            // Get company info
            $perusahaan = Perusahaan::first();

            // Get additional info from request
            $catalogTitle = $request->input('catalog_title', 'DAFTAR HARGA BARANG');
            $validUntil = $request->input('valid_until', date('d/m/Y', strtotime('+30 days')));
            $notes = $request->input('notes', '');
            $showStock = $request->input('show_stock', false);

            // Load PDF view
            $pdf = PDF::loadView('penawaran.pdf', compact(
                'barangs', 
                'perusahaan', 
                'catalogTitle', 
                'validUntil',
                'notes',
                'showStock'
            ));

            // Set paper size and orientation
            $pdf->setPaper('A4', 'portrait');

            // Generate filename
            $filename = 'Penawaran_Harga_' . date('Y-m-d_His') . '.pdf';

            // Download PDF
            return $pdf->download($filename);

        } catch (\Exception $e) {
            Log::error('Error exporting catalog to PDF:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()->with('error', 'Gagal export PDF: ' . $e->getMessage());
        }
    }

    /**
     * Export selected items to Excel
     */
    public function exportExcel(Request $request)
    {
        try {
            $selectedItemsJson = $request->input('selected_items', '[]');
            $selectedIds = json_decode($selectedItemsJson, true);
            
            if (empty($selectedIds) || !is_array($selectedIds)) {
                return back()->with('error', 'Pilih minimal 1 barang untuk di-export');
            }

            // Get selected items
            $barangs = KodeBarang::whereIn('id', $selectedIds)
                ->orderBy('kode_barang', 'asc')
                ->get();

            // Get company info
            $perusahaan = Perusahaan::first();

            // Get additional info from request
            $catalogTitle = $request->input('catalog_title', 'DAFTAR HARGA BARANG');
            $showStock = $request->input('show_stock', false);

            // Export using Laravel Excel
            return \Maatwebsite\Excel\Facades\Excel::download(
                new \App\Exports\PenawaranHargaExport($barangs, $perusahaan, $catalogTitle, $showStock),
                'Penawaran_Harga_' . date('Y-m-d_His') . '.xlsx'
            );

        } catch (\Exception $e) {
            Log::error('Error exporting catalog to Excel:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()->with('error', 'Gagal export Excel: ' . $e->getMessage());
        }
    }
}

