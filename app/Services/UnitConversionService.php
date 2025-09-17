<?php

namespace App\Services;

use App\Models\KodeBarang;
use App\Models\UnitConversion;
use App\Models\CustomerPrice;
use App\Models\Customer;
use Exception;

class UnitConversionService
{
    /**
     * Konversi quantity dari unit turunan ke unit dasar
     * 
     * @param int $kodeBarangId
     * @param float $qty
     * @param string $fromUnit
     * @param bool $roundUp Apakah pembulatan ke atas (default: true untuk stok)
     * @return float
     */
    public function convertToBaseUnit(int $kodeBarangId, float $qty, string $fromUnit, bool $roundUp = true): float
    {
        $kodeBarang = KodeBarang::find($kodeBarangId);
        if (!$kodeBarang) {
            throw new Exception("Kode barang tidak ditemukan");
        }

        // Jika unit sudah sama dengan unit dasar, return qty as is
        if ($fromUnit === $kodeBarang->unit_dasar) {
            return $qty;
        }

        // Cari konversi unit
        $conversion = UnitConversion::where('kode_barang_id', $kodeBarangId)
            ->where('unit_turunan', $fromUnit)
            ->active()
            ->first();

        if (!$conversion) {
            // Tolerant fallback: treat unknown unit as base unit (no conversion)
            return $qty;
        }

        $result = $qty * $conversion->nilai_konversi;
        
        // Pembulatan untuk menghindari pecahan
        if ($roundUp) {
            return ceil($result); // Pembulatan ke atas untuk stok
        } else {
            return round($result, 2); // Pembulatan normal untuk harga
        }
    }

    /**
     * Konversi quantity dari unit dasar ke unit turunan
     * 
     * @param int $kodeBarangId
     * @param float $qty
     * @param string $toUnit
     * @param bool $roundUp Apakah pembulatan ke atas (default: false untuk penjualan)
     * @return float
     */
    public function convertFromBaseUnit(int $kodeBarangId, float $qty, string $toUnit, bool $roundUp = false): float
    {
        $kodeBarang = KodeBarang::find($kodeBarangId);
        if (!$kodeBarang) {
            throw new Exception("Kode barang tidak ditemukan");
        }

        // Jika unit sudah sama dengan unit dasar, return qty as is
        if ($toUnit === $kodeBarang->unit_dasar) {
            return $qty;
        }

        // Cari konversi unit
        $conversion = UnitConversion::where('kode_barang_id', $kodeBarangId)
            ->where('unit_turunan', $toUnit)
            ->active()
            ->first();

        if (!$conversion) {
            // Tolerant fallback: treat unknown unit as base unit (no conversion)
            return $qty;
        }

        $result = $qty / $conversion->nilai_konversi;
        
        // Pembulatan untuk menghindari pecahan
        if ($roundUp) {
            return ceil($result); // Pembulatan ke atas
        } else {
            return round($result, 2); // Pembulatan normal
        }
    }

    /**
     * Konversi quantity dengan aturan pembulatan khusus untuk lusin
     * 
     * @param int $kodeBarangId
     * @param float $qty
     * @param string $fromUnit
     * @param string $toUnit
     * @param string $roundingRule 'up', 'down', 'normal'
     * @return float
     */
    public function convertWithRounding(int $kodeBarangId, float $qty, string $fromUnit, string $toUnit, string $roundingRule = 'normal'): float
    {
        $kodeBarang = KodeBarang::find($kodeBarangId);
        if (!$kodeBarang) {
            throw new Exception("Kode barang tidak ditemukan");
        }

        // Jika unit sama, return qty as is
        if ($fromUnit === $toUnit) {
            return $qty;
        }

        // Cari konversi unit dari fromUnit ke toUnit
        $conversion = UnitConversion::where('kode_barang_id', $kodeBarangId)
            ->where('unit_turunan', $fromUnit)
            ->active()
            ->first();

        if (!$conversion) {
            // Jika tidak ada konversi, coba sebaliknya
            $conversion = UnitConversion::where('kode_barang_id', $kodeBarangId)
                ->where('unit_turunan', $toUnit)
                ->active()
                ->first();
            
            if (!$conversion) {
                return $qty;
            }
            
            // Konversi terbalik
            $result = $qty / $conversion->nilai_konversi;
        } else {
            // Konversi normal
            $result = $qty * $conversion->nilai_konversi;
        }
        
        // Terapkan aturan pembulatan
        switch ($roundingRule) {
            case 'up':
                return ceil($result);
            case 'down':
                return floor($result);
            case 'normal':
            default:
                return round($result, 2);
        }
    }

    /**
     * Dapatkan harga jual untuk customer tertentu
     * 
     * @param int $customerId
     * @param int $kodeBarangId
     * @param string $unit
     * @return array
     */
    public function getCustomerPrice(int $customerId, int $kodeBarangId, string $unit = 'LBR'): array
    {
        $kodeBarang = KodeBarang::find($kodeBarangId);
        if (!$kodeBarang) {
            throw new Exception("Kode barang tidak ditemukan");
        }

        // Cari harga khusus customer
        $customerPrice = CustomerPrice::where('customer_id', $customerId)
            ->where('kode_barang_id', $kodeBarangId)
            ->active()
            ->first();

        if ($customerPrice) {
            $hargaJual = $customerPrice->harga_jual_khusus;
            $ongkosKuli = $customerPrice->ongkos_kuli_khusus ?? $kodeBarang->ongkos_kuli_default;
            $unitJual = $customerPrice->unit_jual;
        } else {
            // Gunakan harga default
            $hargaJual = $kodeBarang->harga_jual;
            $ongkosKuli = $kodeBarang->ongkos_kuli_default;
            $unitJual = $kodeBarang->unit_dasar;
        }

        // Konversi harga jika unit berbeda
        if ($unit !== $unitJual) {
            $hargaJual = $this->convertPrice($kodeBarangId, $hargaJual, $unitJual, $unit);
        }

        return [
            'harga_jual' => $hargaJual,
            'ongkos_kuli' => $ongkosKuli,
            'unit' => $unit,
            'is_custom_price' => $customerPrice !== null
        ];
    }

    /**
     * Konversi harga antar unit
     * 
     * @param int $kodeBarangId
     * @param float $harga
     * @param string $fromUnit
     * @param string $toUnit
     * @return float
     */
    public function convertPrice(int $kodeBarangId, float $harga, string $fromUnit, string $toUnit): float
    {
        $kodeBarang = KodeBarang::find($kodeBarangId);
        if (!$kodeBarang) {
            throw new Exception("Kode barang tidak ditemukan");
        }

        // Jika unit sama, return harga as is
        if ($fromUnit === $toUnit) {
            return $harga;
        }

        // Konversi ke unit dasar dulu
        $qtyInBaseUnit = $this->convertToBaseUnit($kodeBarangId, 1, $fromUnit);
        $hargaPerBaseUnit = $harga / max($qtyInBaseUnit, 1e-9);

        // Konversi dari unit dasar ke unit target
        $qtyInTargetUnit = $this->convertToBaseUnit($kodeBarangId, 1, $toUnit);
        
        // Harga per unit target = harga per unit dasar * (1 unit target dalam unit dasar)
        // Karena 1 LUSIN = 12 PCS, maka harga per LUSIN = harga per PCS * 12
        return $hargaPerBaseUnit * $qtyInTargetUnit;
    }

    /**
     * Dapatkan semua unit yang tersedia untuk barang tertentu
     * 
     * @param int $kodeBarangId
     * @return array
     */
    public function getAvailableUnits(int $kodeBarangId): array
    {
        $kodeBarang = KodeBarang::find($kodeBarangId);
        if (!$kodeBarang) {
            return [];
        }

        $units = [$kodeBarang->unit_dasar];

        $conversions = UnitConversion::where('kode_barang_id', $kodeBarangId)
            ->active()
            ->get();

        foreach ($conversions as $conversion) {
            $units[] = $conversion->unit_turunan;
        }

        return array_unique($units);
    }

    /**
     * Validasi apakah unit valid untuk barang tertentu
     * 
     * @param int $kodeBarangId
     * @param string $unit
     * @return bool
     */
    public function isValidUnit(int $kodeBarangId, string $unit): bool
    {
        $availableUnits = $this->getAvailableUnits($kodeBarangId);
        return in_array($unit, $availableUnits);
    }
}
