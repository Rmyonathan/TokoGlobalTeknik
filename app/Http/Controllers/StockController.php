<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Stock;
use App\Models\StockMutation;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Support\Facades\Log;

class StockController extends Controller
{
    /**
     * Display the stock mutation report
     */
    public function mutasiStock(Request $request)
    {
        // Get filter parameters
        $kolom = $request->input('kolom', 'kode_barang');
        $value = $request->input('value');
        $tanggal_awal = $request->input('tanggal_awal');
        $tanggal_akhir = $request->input('tanggal_akhir');
        
        // Get all available stocks
        $stocksQuery = Stock::select(
            'stocks.kode_barang',
            'stocks.nama_barang',
            'stocks.good_stock',
            'stocks.bad_stock',
            'stocks.so',
            'stocks.satuan'
        );
        
        // Apply filters if provided
        if ($value) {
            if ($kolom === 'kode_barang') {
                $stocksQuery->where('stocks.kode_barang', 'like', "%{$value}%");
            } elseif ($kolom === 'nama') {
                $stocksQuery->where('stocks.nama_barang', 'like', "%{$value}%");
            }
        }
        
        $stocks = $stocksQuery->get();
        
        // Get mutations for the filtered products
        $mutations = collect([]);
        $openingBalance = 0;
        $selectedStock = null;
        
        // Check if we should show mutations (either a single result or user clicked on an item)
        $selectedKodeBarang = $request->input('selected_kode_barang');
        
        if ($selectedKodeBarang) {
            // User specifically selected an item to view mutations for
            $selectedStock = $stocks->where('kode_barang', $selectedKodeBarang)->first();
        } elseif ($stocks->count() == 1) {
            // Only one stock item found in search, automatically show its mutations
            $selectedStock = $stocks->first();
        }
        
        if ($selectedStock) {
            // Build query for mutations
            $mutationsQuery = StockMutation::where('kode_barang', $selectedStock->kode_barang)
                ->where('so', $selectedStock->so)
                ->orderBy('tanggal')
                ->orderBy('id');
            
            // Apply date filters if provided
            if ($tanggal_awal) {
                // Get opening balance for the specified date
                $openingBalance = $this->getOpeningBalance(
                    $selectedStock->kode_barang,
                    $selectedStock->so, 
                    $tanggal_awal
                );
                
                $mutationsQuery->whereDate('tanggal', '>=', $tanggal_awal);
            }
            
            if ($tanggal_akhir) {
                $mutationsQuery->whereDate('tanggal', '<=', $tanggal_akhir);
            }
            
            $mutations = $mutationsQuery->get();
        }
        
        return view('stock.mutasi_stock', compact(
            'stocks', 
            'mutations', 
            'openingBalance', 
            'kolom', 
            'value', 
            'tanggal_awal', 
            'tanggal_akhir',
            'selectedStock'
        ));
    }
    
    /**
     * Print good stock report
     */
    public function printGoodStock(Request $request)
    {
        $kolom = $request->input('kolom', 'kode_barang');
        $value = $request->input('value');
        
        $query = Stock::select(
            'stocks.kode_barang',
            'stocks.nama_barang',
            'stocks.good_stock',
            'stocks.satuan',
            'stocks.so'
        );
        
        if ($value) {
            if ($kolom === 'kode_barang') {
                $query->where('stocks.kode_barang', 'like', "%{$value}%");
            } elseif ($kolom === 'nama') {
                $query->where('stocks.nama_barang', 'like', "%{$value}%");
            }
        }
        
        $stocks = $query->get();
        
        return view('stock.print_good_stock', compact('stocks'));
    }
    
    /**
     * Record a purchase for stock mutation report only (no panel inventory update)
     */
    public function recordPurchase(
        string $kode_barang,
        string $nama_barang,
        string $no_transaksi,
        string $tanggal,
        string $no_nota,
        string $supplier_customer,
        float $quantity,
        string $so = 'ALUMKA',
        string $satuan = 'LBR'
    ): bool {
        try {
            DB::beginTransaction();

            // Get current stock level or create new record if it doesn't exist
            $stock = Stock::firstOrCreate(
                ['kode_barang' => $kode_barang, 'so' => $so],
                [
                    'nama_barang' => $nama_barang,
                    'good_stock' => 0,
                    'satuan' => $satuan
                ]
            );
            
            // Calculate new total
            $newTotal = $stock->good_stock + $quantity;
            
            // Record the stock movement
            StockMutation::create([
                'kode_barang' => $kode_barang,
                'nama_barang' => $nama_barang,
                'no_transaksi' => $no_transaksi,
                'tanggal' => $tanggal,
                'no_nota' => $no_nota,
                'supplier_customer' => $supplier_customer,
                'plus' => $quantity, // Always positive for purchases
                'minus' => 0,
                'total' => $newTotal,
                'so' => $so,
                'satuan' => $satuan,
                'keterangan' => 'Purchase transaction',
            ]);

            // Update stock
            $stock->good_stock = $newTotal;
            $stock->save();
            
            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error recording purchase:', ['message' => $e->getMessage()]);
            return false;
        }
    }
    
    /**
     * Record a sale for stock mutation report only (no panel inventory update)
     */
    public function recordSale(
        string $kode_barang,
        string $nama_barang,
        string $no_transaksi,
        string $tanggal,
        string $no_nota,
        string $customer,
        float $quantity,
        string $so = 'ALUMKA',
        string $satuan = 'LBR'
    ): bool {
        try {
            DB::beginTransaction();

            // Get current stock level or create new record if it doesn't exist
            $stock = Stock::firstOrCreate(
                ['kode_barang' => $kode_barang, 'so' => $so],
                [
                    'nama_barang' => $nama_barang,
                    'good_stock' => 0,
                    'satuan' => $satuan
                ]
            );
            
            // Calculate new total
            $newTotal = $stock->good_stock - $quantity;
            
            // Record the stock movement
            StockMutation::create([
                'kode_barang' => $kode_barang,
                'nama_barang' => $nama_barang,
                'no_transaksi' => $no_transaksi,
                'tanggal' => $tanggal,
                'no_nota' => $no_nota,
                'supplier_customer' => $customer,
                'plus' => 0,
                'minus' => $quantity, // Always positive value for sales
                'total' => $newTotal,
                'so' => $so,
                'satuan' => $satuan,
                'keterangan' => 'Sale transaction',
            ]);

            // Update stock
            $stock->good_stock = $newTotal;
            $stock->save();
            
            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error recording sale:', ['message' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Get the opening balance for a product on a specific date
     */
    public function getOpeningBalance(string $kode_barang, string $so, string $date): float
    {
        $latestMovement = StockMutation::where('kode_barang', $kode_barang)
            ->where('so', $so)
            ->whereDate('tanggal', '<', $date)
            ->orderBy('tanggal', 'desc')
            ->orderBy('id', 'desc')
            ->first();
            
        return $latestMovement ? $latestMovement->total : 0;
    }
    
    /**
     * API endpoint to get stock data for a specific product
     */
    public function getStock(Request $request)
    {
        $kode_barang = $request->input('kode_barang');
        $so = $request->input('so', 'ALUMKA');
        
        $stock = Stock::where('kode_barang', $kode_barang)
            ->where('so', $so)
            ->first();
            
        if (!$stock) {
            return response()->json([
                'success' => false,
                'message' => 'Stock not found'
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'data' => $stock
        ]);
    }
    
    /**
     * API endpoint to get mutations for a specific product
     */
    public function getStockMutations(Request $request)
    {
        $kode_barang = $request->input('kode_barang');
        $so = $request->input('so', 'ALUMKA');
        $tanggal_awal = $request->input('tanggal_awal');
        $tanggal_akhir = $request->input('tanggal_akhir');
        
        $query = StockMutation::where('kode_barang', $kode_barang)
            ->where('so', $so)
            ->orderBy('tanggal')
            ->orderBy('id');
            
        if ($tanggal_awal) {
            $query->whereDate('tanggal', '>=', $tanggal_awal);
        }
        
        if ($tanggal_akhir) {
            $query->whereDate('tanggal', '<=', $tanggal_akhir);
        }
        
        $mutations = $query->get();
        $openingBalance = 0;
        
        if ($tanggal_awal) {
            $openingBalance = $this->getOpeningBalance($kode_barang, $so, $tanggal_awal);
        }
        
        return response()->json([
            'success' => true,
            'opening_balance' => $openingBalance,
            'data' => $mutations
        ]);
    }
}