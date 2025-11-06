<?php

namespace App\Services;

use App\Models\StockBatch;
use App\Models\TransaksiItemSumber;
use Illuminate\Support\Facades\DB;
use Exception;

class FifoService
{
    /**
     * Alokasi stok menggunakan metode FIFO untuk Transaksi
     * 
     * @param int $kodeBarangId
     * @param float $qtyDibutuhkan
     * @param int $transaksiItemId
     * @return array
     * @throws Exception
     */
    public function alokasiStok(int $kodeBarangId, float $qtyDibutuhkan, int $transaksiItemId): array
    {
        DB::beginTransaction();
        
        try {
            // Ambil batch yang tersisa, urutkan berdasarkan FIFO
            $batches = StockBatch::byKodeBarang($kodeBarangId)
                ->tersisa()
                ->fifo()
                ->lockForUpdate()
                ->get();

            $qtyTersisa = $qtyDibutuhkan;
            $alokasi = [];
            $totalHargaModal = 0;

            foreach ($batches as $batch) {
                if ($qtyTersisa <= 0) break;

                $qtyTersedia = $batch->qty_sisa;
                $qtyAmbil = min($qtyTersisa, $qtyTersedia);

                // Buat record transaksi item sumber
                TransaksiItemSumber::create([
                    'transaksi_item_id' => $transaksiItemId,
                    'stock_batch_id' => $batch->id,
                    'qty_diambil' => $qtyAmbil,
                    'harga_modal' => $batch->harga_beli
                ]);

                // Update qty_sisa di batch
                $batch->qty_sisa -= $qtyAmbil;
                $batch->save();

                $alokasi[] = [
                    'batch_id' => $batch->id,
                    'qty_ambil' => $qtyAmbil,
                    'harga_modal' => $batch->harga_beli
                ];

                $totalHargaModal += ($qtyAmbil * $batch->harga_beli);
                $qtyTersisa -= $qtyAmbil;
            }

            // Jika masih ada kekurangan, ambil dari global stock
            if ($qtyTersisa > 0) {
                $kodeBarang = \App\Models\KodeBarang::find($kodeBarangId);
                if ($kodeBarang) {
                    $globalStock = \App\Models\Stock::getGlobalStock($kodeBarang->kode_barang);
                    $stokGlobal = $globalStock->good_stock ?? 0;
                    
                    // \Log::info('Using global stock for remaining qty:', [
                    //     'kode_barang' => $kodeBarang->kode_barang,
                    //     'qty_tersisa' => $qtyTersisa,
                    //     'global_stock_available' => $stokGlobal
                    // ]);
                    
                    if ($stokGlobal >= $qtyTersisa) {
                        // Ambil dari global stock
                        $qtyAmbilFromGlobal = $qtyTersisa;
                        
                        // Kurangi global stock di semua database
                        $this->reduceGlobalStock($kodeBarang->kode_barang, $qtyAmbilFromGlobal);
                        
                        // Assume average harga_modal = 0 untuk stock dari global (bisa disesuaikan)
                        $alokasi[] = [
                            'batch_id' => null,
                            'qty_ambil' => $qtyAmbilFromGlobal,
                            'harga_modal' => 0,
                            'source' => 'global_stock'
                        ];
                        
                        $qtyTersisa = 0;
                    } else {
                        throw new Exception("Stok tidak mencukupi. Kekurangan: " . ($qtyTersisa - $stokGlobal));
                    }
                } else {
                    throw new Exception("Stok tidak mencukupi. Kekurangan: {$qtyTersisa}");
                }
            }

            DB::commit();

            return [
                'success' => true,
                'alokasi' => $alokasi,
                'total_harga_modal' => $totalHargaModal,
                'rata_rata_harga_modal' => $qtyDibutuhkan > 0 ? $totalHargaModal / $qtyDibutuhkan : 0
            ];

        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Alokasi stok menggunakan metode FIFO untuk Surat Jalan (tanpa membuat TransaksiItemSumber)
     * 
     * @param int $kodeBarangId
     * @param float $qtyDibutuhkan
     * @param int $suratJalanItemId
     * @return array
     * @throws Exception
     */
    public function alokasiStokUntukSuratJalan(int $kodeBarangId, float $qtyDibutuhkan, int $suratJalanItemId): array
    {
        DB::beginTransaction();
        
        try {
            // Ambil batch yang tersisa, urutkan berdasarkan FIFO
            $batches = StockBatch::byKodeBarang($kodeBarangId)
                ->tersisa()
                ->fifo()
                ->lockForUpdate()
                ->get();

            $qtyTersisa = $qtyDibutuhkan;
            $alokasi = [];
            $totalHargaModal = 0;

            foreach ($batches as $batch) {
                if ($qtyTersisa <= 0) break;

                $qtyTersedia = $batch->qty_sisa;
                $qtyAmbil = min($qtyTersisa, $qtyTersedia);

                // Update qty_sisa di batch (tanpa membuat TransaksiItemSumber)
                $batch->qty_sisa -= $qtyAmbil;
                $batch->save();

                $alokasi[] = [
                    'batch_id' => $batch->id,
                    'qty_ambil' => $qtyAmbil,
                    'harga_modal' => $batch->harga_beli
                ];

                $totalHargaModal += ($qtyAmbil * $batch->harga_beli);
                $qtyTersisa -= $qtyAmbil;
            }

            // Jika masih ada kekurangan, ambil dari global stock
            if ($qtyTersisa > 0) {
                $kodeBarang = \App\Models\KodeBarang::find($kodeBarangId);
                if ($kodeBarang) {
                    $globalStock = \App\Models\Stock::getGlobalStock($kodeBarang->kode_barang);
                    $stokGlobal = $globalStock->good_stock ?? 0;
                    
                    \Log::info('Using global stock for SJ remaining qty:', [
                        'kode_barang' => $kodeBarang->kode_barang,
                        'qty_tersisa' => $qtyTersisa,
                        'global_stock_available' => $stokGlobal
                    ]);
                    
                    if ($stokGlobal >= $qtyTersisa) {
                        // Ambil dari global stock
                        $qtyAmbilFromGlobal = $qtyTersisa;
                        
                        // Kurangi global stock di semua database
                        $this->reduceGlobalStock($kodeBarang->kode_barang, $qtyAmbilFromGlobal);
                        
                        $alokasi[] = [
                            'batch_id' => null,
                            'qty_ambil' => $qtyAmbilFromGlobal,
                            'harga_modal' => 0,
                            'source' => 'global_stock'
                        ];
                        
                        $qtyTersisa = 0;
                    } else {
                        throw new Exception("Stok tidak mencukupi. Kekurangan: " . ($qtyTersisa - $stokGlobal));
                    }
                } else {
                    throw new Exception("Stok tidak mencukupi. Kekurangan: {$qtyTersisa}");
                }
            }

            DB::commit();

            return [
                'success' => true,
                'alokasi' => $alokasi,
                'total_harga_modal' => $totalHargaModal,
                'rata_rata_harga_modal' => $qtyDibutuhkan > 0 ? $totalHargaModal / $qtyDibutuhkan : 0
            ];

        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Hitung rata-rata harga modal untuk barang tertentu
     * 
     * @param int $kodeBarangId
     * @return float
     */
    public function hitungRataRataHargaModal(int $kodeBarangId): float
    {
        $batch = StockBatch::byKodeBarang($kodeBarangId)
            ->tersisa()
            ->selectRaw('SUM(qty_sisa * harga_beli) as total_value, SUM(qty_sisa) as total_qty')
            ->first();

        if (!$batch || $batch->total_qty <= 0) {
            return 0;
        }

        return $batch->total_value / $batch->total_qty;
    }

    /**
     * Kurangi global stock di semua database
     * 
     * @param string $kodeBarang
     * @param float $qty
     * @return void
     */
    private function reduceGlobalStock(string $kodeBarang, float $qty): void
    {
        try {
            // Reduce from primary database
            $primaryStock = \App\Models\Stock::onDatabase('primary')
                ->where('kode_barang', $kodeBarang)
                ->first();
            
            if ($primaryStock && $primaryStock->good_stock > 0) {
                $qtyToReducePrimary = min($qty, $primaryStock->good_stock);
                $primaryStock->good_stock -= $qtyToReducePrimary;
                $primaryStock->save();
                $qty -= $qtyToReducePrimary;
                
                \Log::info('Reduced stock from primary DB:', [
                    'kode_barang' => $kodeBarang,
                    'qty_reduced' => $qtyToReducePrimary,
                    'remaining_stock' => $primaryStock->good_stock
                ]);
            }
        } catch (\Exception $e) {
            \Log::warning('Could not reduce stock from primary DB:', ['message' => $e->getMessage()]);
        }
        
        // If still need to reduce more, reduce from secondary
        if ($qty > 0) {
            try {
                $secondaryStock = \App\Models\Stock::onDatabase('secondary')
                    ->where('kode_barang', $kodeBarang)
                    ->first();
                
                if ($secondaryStock && $secondaryStock->good_stock > 0) {
                    $qtyToReduceSecondary = min($qty, $secondaryStock->good_stock);
                    $secondaryStock->good_stock -= $qtyToReduceSecondary;
                    $secondaryStock->save();
                    
                    \Log::info('Reduced stock from secondary DB:', [
                        'kode_barang' => $kodeBarang,
                        'qty_reduced' => $qtyToReduceSecondary,
                        'remaining_stock' => $secondaryStock->good_stock
                    ]);
                }
            } catch (\Exception $e) {
                \Log::warning('Could not reduce stock from secondary DB:', ['message' => $e->getMessage()]);
            }
        }
    }

    /**
     * Dapatkan stok tersedia untuk barang tertentu
     * 
     * @param int $kodeBarangId
     * @return float
     */
    public function getStokTersedia(int $kodeBarangId): float
    {
        // Get stock from FIFO batches
        $stokBatch = StockBatch::byKodeBarang($kodeBarangId)
            ->tersisa()
            ->sum('qty_sisa');
        
        // Get stock from global stock table
        $kodeBarang = \App\Models\KodeBarang::find($kodeBarangId);
        if ($kodeBarang) {
            $globalStock = \App\Models\Stock::getGlobalStock($kodeBarang->kode_barang);
            $stokGlobal = $globalStock->good_stock ?? 0;
        } else {
            $stokGlobal = 0;
        }
        
        // Combine both sources
        $totalStok = $stokBatch + $stokGlobal;
        
        \Log::info('Combined Stock Check:', [
            'kode_barang_id' => $kodeBarangId,
            'stock_batches' => $stokBatch,
            'global_stock' => $stokGlobal,
            'total_available' => $totalStok
        ]);
        
        return $totalStok;
    }

    /**
     * Dapatkan detail batch untuk barang tertentu
     * 
     * @param int $kodeBarangId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getBatchDetail(int $kodeBarangId)
    {
        return StockBatch::byKodeBarang($kodeBarangId)
            ->with(['pembelianItem.pembelian.supplierRelation'])
            ->orderBy('tanggal_masuk', 'asc')
            ->get();
    }

    /**
     * Tambah stok untuk return barang dari penjualan
     * 
     * @param int $kodeBarangId
     * @param float $qty
     * @param float $harga
     * @param string $keterangan
     * @param string $customerId
     * @return bool
     * @throws Exception
     */
    public function addStock(int $kodeBarangId, float $qty, float $harga, string $keterangan, string $customerId = null): bool
    {
        DB::beginTransaction();
        
        try {
            // Cari batch yang sudah ada dengan harga yang sama
            $existingBatch = StockBatch::byKodeBarang($kodeBarangId)
                ->where('harga_beli', $harga)
                ->where('qty_sisa', '>', 0)
                ->first();

            if ($existingBatch) {
                // Tambah ke batch yang sudah ada
                $existingBatch->qty_sisa += $qty;
                $existingBatch->save();
            } else {
                // Buat batch baru untuk return
                StockBatch::create([
                    'kode_barang_id' => $kodeBarangId,
                    'qty_masuk' => $qty,
                    'qty_sisa' => $qty,
                    'harga_beli' => $harga,
                    'tanggal_masuk' => now(),
                    'keterangan' => $keterangan,
                    'customer_id' => $customerId,
                    'tipe_batch' => 'return_penjualan'
                ]);
            }

            // Update tabel stocks untuk sinkronisasi dengan master barang
            $kodeBarang = \App\Models\KodeBarang::find($kodeBarangId);
            if ($kodeBarang) {
                $stock = \App\Models\Stock::where('kode_barang', $kodeBarang->kode_barang)->first();
                if ($stock) {
                    $stock->good_stock += $qty;
                    $stock->save();
                }
            }

            DB::commit();
            return true;

        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Kurangi stok untuk return barang ke supplier
     * 
     * @param int $kodeBarangId
     * @param float $qty
     * @param string $keterangan
     * @param string $customerId
     * @return bool
     * @throws Exception
     */
    public function reduceStock(int $kodeBarangId, float $qty, string $keterangan, string $customerId = null): bool
    {
        DB::beginTransaction();
        
        try {
            // Ambil batch yang tersisa, urutkan berdasarkan FIFO
            $batches = StockBatch::byKodeBarang($kodeBarangId)
                ->tersisa()
                ->fifo()
                ->lockForUpdate()
                ->get();

            $qtyTersisa = $qty;

            foreach ($batches as $batch) {
                if ($qtyTersisa <= 0) break;

                $qtyTersedia = $batch->qty_sisa;
                $qtyAmbil = min($qtyTersisa, $qtyTersedia);

                // Update qty_sisa di batch
                $batch->qty_sisa -= $qtyAmbil;
                $batch->save();

                $qtyTersisa -= $qtyAmbil;
            }

            if ($qtyTersisa > 0) {
                throw new Exception("Stok tidak mencukupi untuk return. Kekurangan: {$qtyTersisa}");
            }

            // Update tabel stocks untuk sinkronisasi dengan master barang
            $kodeBarang = \App\Models\KodeBarang::find($kodeBarangId);
            if ($kodeBarang) {
                $stock = \App\Models\Stock::where('kode_barang', $kodeBarang->kode_barang)->first();
                if ($stock) {
                    $stock->good_stock -= $qty;
                    $stock->save();
                }
            }

            DB::commit();
            return true;

        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
