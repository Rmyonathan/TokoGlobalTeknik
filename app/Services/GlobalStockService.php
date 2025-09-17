<?php

namespace App\Services;

use App\Models\Stock;
use App\Models\KodeBarang;
use App\Models\StockMutation;
use Illuminate\Support\Facades\DB;
use Exception;

class GlobalStockService
{
    /**
     * Get all products with global stock information
     */
    public function getAllProductsWithGlobalStock()
    {
        $products = KodeBarang::where('status', 'active')->get();
        
        $productsWithStock = $products->map(function ($product) {
            $globalStock = Stock::getGlobalStock($product->kode_barang);
            $stockBreakdown = Stock::getStockBreakdown($product->kode_barang);
            
            return [
                'id' => $product->id,
                'kode_barang' => $product->kode_barang,
                'name' => $product->name,
                'merek' => $product->merek,
                'ukuran' => $product->ukuran,
                'unit_dasar' => $product->unit_dasar,
                'harga_jual' => $product->harga_jual,
                'global_stock' => $globalStock,
                'stock_breakdown' => $stockBreakdown,
            ];
        });

        return $productsWithStock;
    }

    /**
     * Get stock summary by database
     */
    public function getStockSummaryByDatabase()
    {
        $summary = [];
        $databases = ['primary', 'secondary'];

        foreach ($databases as $db) {
            try {
                $stocks = Stock::onDatabase($db)
                    ->selectRaw('
                        COUNT(*) as total_products,
                        SUM(good_stock) as total_good_stock,
                        SUM(bad_stock) as total_bad_stock,
                        SUM(good_stock + bad_stock) as total_stock
                    ')
                    ->first();

                $summary[$db] = [
                    'database' => $db,
                    'total_products' => $stocks->total_products ?? 0,
                    'total_good_stock' => $stocks->total_good_stock ?? 0,
                    'total_bad_stock' => $stocks->total_bad_stock ?? 0,
                    'total_stock' => $stocks->total_stock ?? 0,
                ];
            } catch (Exception $e) {
                $summary[$db] = [
                    'database' => $db,
                    'total_products' => 0,
                    'total_good_stock' => 0,
                    'total_bad_stock' => 0,
                    'total_stock' => 0,
                    'error' => 'Connection failed'
                ];
            }
        }

        return $summary;
    }

    /**
     * Get stock movement history across all databases
     */
    public function getGlobalStockMovement($kodeBarang, $startDate = null, $endDate = null)
    {
        $movements = collect();
        $databases = ['primary', 'secondary'];

        foreach ($databases as $db) {
            try {
                $query = StockMutation::onDatabase($db)
                    ->where('kode_barang', $kodeBarang)
                    ->with(['transaksi', 'pembelian', 'suratJalan']);

                if ($startDate) {
                    $query->where('tanggal', '>=', $startDate);
                }
                if ($endDate) {
                    $query->where('tanggal', '<=', $endDate);
                }

                $dbMovements = $query->orderBy('tanggal', 'desc')->get();
                
                $dbMovements->each(function ($movement) use ($db) {
                    $movement->database_source = $db;
                });

                $movements = $movements->merge($dbMovements);
            } catch (Exception $e) {
                // Skip if connection fails
                continue;
            }
        }

        return $movements->sortByDesc('tanggal');
    }

    /**
     * Sync stock between databases
     */
    public function syncStockBetweenDatabases($kodeBarang)
    {
        try {
            DB::beginTransaction();

            // Get stock from both databases
            $primaryStock = Stock::onDatabase('primary')
                ->where('kode_barang', $kodeBarang)
                ->first();

            $secondaryStock = Stock::onDatabase('secondary')
                ->where('kode_barang', $kodeBarang)
                ->first();

            if (!$primaryStock && !$secondaryStock) {
                throw new Exception('Produk tidak ditemukan di kedua database');
            }

            // Use primary as master if both exist
            $masterStock = $primaryStock ?: $secondaryStock;
            $targetDatabase = $primaryStock ? 'secondary' : 'primary';

            // Create or update stock in target database
            if ($targetDatabase === 'secondary') {
                if ($secondaryStock) {
                    $secondaryStock->update([
                        'nama_barang' => $masterStock->nama_barang,
                        'good_stock' => $masterStock->good_stock,
                        'bad_stock' => $masterStock->bad_stock,
                        'so' => $masterStock->so,
                        'satuan' => $masterStock->satuan,
                    ]);
                } else {
                    Stock::onDatabase('secondary')->create([
                        'kode_barang' => $masterStock->kode_barang,
                        'nama_barang' => $masterStock->nama_barang,
                        'good_stock' => $masterStock->good_stock,
                        'bad_stock' => $masterStock->bad_stock,
                        'so' => $masterStock->so,
                        'satuan' => $masterStock->satuan,
                    ]);
                }
            } else {
                if ($primaryStock) {
                    $primaryStock->update([
                        'nama_barang' => $masterStock->nama_barang,
                        'good_stock' => $masterStock->good_stock,
                        'bad_stock' => $masterStock->bad_stock,
                        'so' => $masterStock->so,
                        'satuan' => $masterStock->satuan,
                    ]);
                } else {
                    Stock::onDatabase('primary')->create([
                        'kode_barang' => $masterStock->kode_barang,
                        'nama_barang' => $masterStock->nama_barang,
                        'good_stock' => $masterStock->good_stock,
                        'bad_stock' => $masterStock->bad_stock,
                        'so' => $masterStock->so,
                        'satuan' => $masterStock->satuan,
                    ]);
                }
            }

            DB::commit();
            return true;

        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    /**
     * Get low stock products across all databases
     */
    public function getLowStockProducts($threshold = 10)
    {
        $products = KodeBarang::where('status', 'active')->get();
        $lowStockProducts = collect();

        foreach ($products as $product) {
            $globalStock = Stock::getGlobalStock($product->kode_barang);
            
            if ($globalStock->total_stock <= $threshold) {
                $stockBreakdown = Stock::getStockBreakdown($product->kode_barang);
                
                $lowStockProducts->push([
                    'product' => $product,
                    'global_stock' => $globalStock,
                    'stock_breakdown' => $stockBreakdown,
                ]);
            }
        }

        return $lowStockProducts->sortBy('global_stock.total_stock');
    }
}
