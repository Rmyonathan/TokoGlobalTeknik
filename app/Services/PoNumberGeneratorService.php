<?php

namespace App\Services;

use App\Models\SuratJalan;
use App\Models\Transaksi;
use Carbon\Carbon;

class PoNumberGeneratorService
{
    /**
     * Generate PO number for Surat Jalan
     */
    public static function generateForSuratJalan(): string
    {
        $prefix = 'PO-SJ-';
        $year = date('Y');
        $month = date('m');
        
        // Format: PO-SJ-YYYYMM-XXXX
        $baseNumber = $prefix . $year . $month . '-';
        
        // Get last PO number for this month
        $lastPo = SuratJalan::where('no_po', 'like', $baseNumber . '%')
            ->orderBy('no_po', 'desc')
            ->first();
        
        if ($lastPo) {
            // Extract number from last PO
            $lastNumber = (int) substr($lastPo->no_po, strlen($baseNumber));
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }
        
        return $baseNumber . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }
    
    /**
     * Generate PO number for Transaksi
     */
    public static function generateForTransaksi(): string
    {
        $prefix = 'PO-TRX-';
        $year = date('Y');
        $month = date('m');
        
        // Format: PO-TRX-YYYYMM-XXXX
        $baseNumber = $prefix . $year . $month . '-';
        
        // Get last PO number for this month
        $lastPo = Transaksi::where('no_po', 'like', $baseNumber . '%')
            ->orderBy('no_po', 'desc')
            ->first();
        
        if ($lastPo) {
            // Extract number from last PO
            $lastNumber = (int) substr($lastPo->no_po, strlen($baseNumber));
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }
        
        return $baseNumber . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }
    
    /**
     * Generate PO number with custom prefix
     */
    public static function generateWithPrefix(string $prefix, string $table = 'surat_jalan'): string
    {
        $year = date('Y');
        $month = date('m');
        
        // Format: PREFIX-YYYYMM-XXXX
        $baseNumber = $prefix . '-' . $year . $month . '-';
        
        // Get last PO number for this month
        $query = $table === 'surat_jalan' ? SuratJalan::class : Transaksi::class;
        $lastPo = $query::where('no_po', 'like', $baseNumber . '%')
            ->orderBy('no_po', 'desc')
            ->first();
        
        if ($lastPo) {
            // Extract number from last PO
            $lastNumber = (int) substr($lastPo->no_po, strlen($baseNumber));
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }
        
        return $baseNumber . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }
    
    /**
     * Generate PO number for specific date
     */
    public static function generateForDate(string $date, string $type = 'surat_jalan'): string
    {
        $prefix = $type === 'surat_jalan' ? 'PO-SJ-' : 'PO-TRX-';
        $carbonDate = Carbon::parse($date);
        $year = $carbonDate->format('Y');
        $month = $carbonDate->format('m');
        
        // Format: PO-SJ-YYYYMM-XXXX or PO-TRX-YYYYMM-XXXX
        $baseNumber = $prefix . $year . $month . '-';
        
        // Get last PO number for this month
        $query = $type === 'surat_jalan' ? SuratJalan::class : Transaksi::class;
        $lastPo = $query::where('no_po', 'like', $baseNumber . '%')
            ->orderBy('no_po', 'desc')
            ->first();
        
        if ($lastPo) {
            // Extract number from last PO
            $lastNumber = (int) substr($lastPo->no_po, strlen($baseNumber));
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }
        
        return $baseNumber . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }
    
    /**
     * Check if PO number is unique
     */
    public static function isUnique(string $poNumber, string $table = 'surat_jalan'): bool
    {
        $query = $table === 'surat_jalan' ? SuratJalan::class : Transaksi::class;
        return !$query::where('no_po', $poNumber)->exists();
    }
    
    /**
     * Get next PO number without saving
     */
    public static function getNextNumber(string $type = 'surat_jalan'): string
    {
        return $type === 'surat_jalan' ? self::generateForSuratJalan() : self::generateForTransaksi();
    }
}
