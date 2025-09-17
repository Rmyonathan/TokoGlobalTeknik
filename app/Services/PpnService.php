<?php

namespace App\Services;

use App\Models\Perusahaan;
use Illuminate\Support\Facades\DB;

class PpnService
{
    /**
     * Get PPN configuration from active company
     */
    public static function getPpnConfig(): array
    {
        $perusahaan = Perusahaan::getActive();
        $connection = DB::getDefaultConnection();
        $isDb2 = ($connection === 'mysql_second');
        
        if (!$perusahaan) {
            return [
                'enabled' => false,
                'rate' => 0
            ];
        }

        // Hanya aktif di DB2 (mysql_second) dan jika flag perusahaan aktif
        $flagEnabled = $perusahaan->ppn_enabled ?? ($perusahaan->is_ppn_enabled ?? false);
        $enabled = $isDb2 && $flagEnabled;

        return [
            'enabled' => $enabled,
            'rate' => $enabled ? ($perusahaan->ppn_rate ?? 11) : 0
        ];
    }

    /**
     * Calculate PPN amount from subtotal
     */
    public static function calculatePpn(float $subtotal): float
    {
        $config = self::getPpnConfig();
        
        if (!$config['enabled'] || $config['rate'] <= 0) {
            return 0;
        }

        return ($subtotal * $config['rate']) / 100;
    }

    /**
     * Calculate grand total including PPN
     */
    public static function calculateGrandTotal(float $subtotal, float $discount = 0, float $discRupiah = 0): array
    {
        $config = self::getPpnConfig();
        
        // Calculate subtotal after discount
        $subtotalAfterDiscount = $subtotal - $discount - $discRupiah;
        
        // Calculate PPN
        $ppn = 0;
        if ($config['enabled'] && $config['rate'] > 0) {
            $ppn = ($subtotalAfterDiscount * $config['rate']) / 100;
        }
        
        // Calculate grand total
        $grandTotal = $subtotalAfterDiscount + $ppn;
        
        return [
            'subtotal' => $subtotal,
            'discount' => $discount,
            'disc_rupiah' => $discRupiah,
            'subtotal_after_discount' => $subtotalAfterDiscount,
            'ppn_enabled' => $config['enabled'],
            'ppn_rate' => $config['rate'],
            'ppn' => $ppn,
            'grand_total' => $grandTotal
        ];
    }

    /**
     * Get PPN rate for display
     */
    public static function getPpnRate(): float
    {
        $config = self::getPpnConfig();
        return $config['rate'];
    }

    /**
     * Check if PPN is enabled
     */
    public static function isPpnEnabled(): bool
    {
        $config = self::getPpnConfig();
        return $config['enabled'];
    }
}
