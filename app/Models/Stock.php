<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\MultiDatabaseTrait;

class Stock extends Model
{
    use HasFactory, MultiDatabaseTrait;

    protected $fillable = [
        'kode_barang',
        'nama_barang',
        'good_stock',
        'bad_stock',
        'so',
        'satuan',
    ];

    protected $casts = [
        'good_stock' => 'float',
        'bad_stock' => 'float',
    ];

    /**
     * Get total stock across all databases
     */
    public static function getGlobalStock($kodeBarang)
    {
        $totalGoodStock = 0;
        $totalBadStock = 0;
        
        try {
            // Get stock from primary database
            $primaryStock = static::onDatabase('primary')
                ->where('kode_barang', $kodeBarang)
                ->first();
            
            if ($primaryStock) {
                $totalGoodStock += $primaryStock->good_stock;
                $totalBadStock += $primaryStock->bad_stock;
            }
        } catch (\Exception $e) {
            // Skip if connection fails
        }

        try {
            // Get stock from secondary database
            $secondaryStock = static::onDatabase('secondary')
                ->where('kode_barang', $kodeBarang)
                ->first();
            
            if ($secondaryStock) {
                $totalGoodStock += $secondaryStock->good_stock;
                $totalBadStock += $secondaryStock->bad_stock;
            }
        } catch (\Exception $e) {
            // Skip if connection fails
        }

        // Get satuan from primary database if available, otherwise from secondary
        $satuan = 'PCS';
        try {
            $primaryStock = static::onDatabase('primary')
                ->where('kode_barang', $kodeBarang)
                ->first();
            if ($primaryStock) {
                $satuan = $primaryStock->satuan;
            } else {
                $secondaryStock = static::onDatabase('secondary')
                    ->where('kode_barang', $kodeBarang)
                    ->first();
                if ($secondaryStock) {
                    $satuan = $secondaryStock->satuan;
                }
            }
        } catch (\Exception $e) {
            // Use default satuan
        }

        return (object) [
            'kode_barang' => $kodeBarang,
            'good_stock' => $totalGoodStock,
            'bad_stock' => $totalBadStock,
            'total_stock' => $totalGoodStock + $totalBadStock,
            'satuan' => $satuan
        ];
    }

    /**
     * Get stock breakdown by database
     */
    public static function getStockBreakdown($kodeBarang)
    {
        $breakdown = [];
        
        $databases = ['primary', 'secondary'];
        
        foreach ($databases as $db) {
            try {
                $stock = static::onDatabase($db)
                    ->where('kode_barang', $kodeBarang)
                    ->first();
                
                if ($stock) {
                    $breakdown[$db] = [
                        'database' => $db,
                        'good_stock' => $stock->good_stock,
                        'bad_stock' => $stock->bad_stock,
                        'total_stock' => $stock->good_stock + $stock->bad_stock,
                        'satuan' => $stock->satuan
                    ];
                } else {
                    $breakdown[$db] = [
                        'database' => $db,
                        'good_stock' => 0,
                        'bad_stock' => 0,
                        'total_stock' => 0,
                        'satuan' => 'PCS'
                    ];
                }
            } catch (\Exception $e) {
                $breakdown[$db] = [
                    'database' => $db,
                    'good_stock' => 0,
                    'bad_stock' => 0,
                    'total_stock' => 0,
                    'satuan' => 'PCS',
                    'error' => 'Connection failed'
                ];
            }
        }

        return $breakdown;
    }

    /**
     * Transfer stock between databases
     */
    public static function transferStock($kodeBarang, $qty, $fromDatabase, $toDatabase, $hargaPerUnit = 0)
    {
        $fromConnection = $fromDatabase === 'primary' ? 'mysql' : 'mysql_second';
        $toConnection = $toDatabase === 'primary' ? 'mysql' : 'mysql_second';

        try {
            // Start transaction
            \DB::beginTransaction();

            // Reduce stock from source database
            $fromStock = static::onDatabase($fromDatabase)
                ->where('kode_barang', $kodeBarang)
                ->first();

            if (!$fromStock || $fromStock->good_stock < $qty) {
                throw new \Exception('Insufficient stock in source database');
            }

            $fromStock->decrement('good_stock', $qty);

            // Add stock to destination database
            $toStock = static::onDatabase($toDatabase)
                ->where('kode_barang', $kodeBarang)
                ->first();

            if ($toStock) {
                $toStock->increment('good_stock', $qty);
            } else {
                // Create new stock record in destination database
                static::onDatabase($toDatabase)->create([
                    'kode_barang' => $kodeBarang,
                    'nama_barang' => $fromStock->nama_barang,
                    'good_stock' => $qty,
                    'bad_stock' => 0,
                    'so' => $fromStock->so,
                    'satuan' => $fromStock->satuan,
                ]);
            }

            // Ensure KodeBarang exists in destination database
            static::ensureKodeBarangExists($kodeBarang, $toDatabase, $fromStock);

            \DB::commit();
            return true;

        } catch (\Exception $e) {
            \DB::rollback();
            throw $e;
        }
    }

    /**
     * Ensure KodeBarang exists in destination database
     */
    private static function ensureKodeBarangExists($kodeBarang, $toDatabase, $fromStock)
    {
        // Get KodeBarang from source database
        $sourceKodeBarang = \App\Models\KodeBarang::where('kode_barang', $kodeBarang)->first();
        
        if (!$sourceKodeBarang) {
            return; // Skip if source KodeBarang doesn't exist
        }

        // Check if KodeBarang exists in destination database
        $toConnection = $toDatabase === 'primary' ? 'mysql' : 'mysql_second';
        $existingKodeBarang = \DB::connection($toConnection)
            ->table('kode_barangs')
            ->where('kode_barang', $kodeBarang)
            ->first();

        if (!$existingKodeBarang) {
            // Create KodeBarang in destination database
            // Include all necessary columns
            $insertData = [
                'kode_barang' => $sourceKodeBarang->kode_barang,
                'name' => $sourceKodeBarang->name,
                'attribute' => $sourceKodeBarang->attribute,
                'cost' => $sourceKodeBarang->cost,
                'harga_jual' => $sourceKodeBarang->harga_jual,
                'unit_dasar' => $sourceKodeBarang->unit_dasar,
                'status' => $sourceKodeBarang->status,
                'created_at' => now(),
                'updated_at' => now(),
            ];
            
            // Add optional columns if they exist in source
            $optionalColumns = [
                'price', 'merek', 'ukuran', 'grup_barang_id', 
                'nilai_konversi', 'satuan_dasar', 'satuan_besar', 
                'ongkos_kuli_default'
            ];
            
            foreach ($optionalColumns as $column) {
                if (isset($sourceKodeBarang->$column)) {
                    $insertData[$column] = $sourceKodeBarang->$column;
                }
            }
            
            \DB::connection($toConnection)->table('kode_barangs')->insert($insertData);
        }
    }
}