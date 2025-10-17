<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CaraBayar extends Model
{
    protected $fillable = ['metode', 'nama', 'coa_account_id'];

    /**
     * Get the COA account associated with this payment method
     */
    public function coaAccount()
    {
        return $this->belongsTo(ChartOfAccount::class, 'coa_account_id');
    }

    /**
     * Check if this payment method has a linked COA account
     */
    public function hasCoaAccount(): bool
    {
        return !is_null($this->coa_account_id);
    }

    /**
     * Get the account type for this payment method
     */
    public function getAccountType(): ?string
    {
        if (!$this->hasCoaAccount()) {
            return null;
        }

        return $this->coaAccount->accountType->name ?? null;
    }

    /**
     * Check if this is a cash payment method
     */
    public function isCash(): bool
    {
        return $this->metode === 'Tunai';
    }

    /**
     * Check if this is a non-cash payment method
     */
    public function isNonCash(): bool
    {
        return $this->metode === 'Non Tunai' || !$this->isCash();
    }

    /**
     * Check if this is a QRIS payment method
     */
    public function isQris(): bool
    {
        return strtoupper($this->nama) === 'QRIS';
    }

    /**
     * Check if this is an EDC payment method
     */
    public function isEdc(): bool
    {
        return strtoupper($this->nama) === 'EDC';
    }

    /**
     * Check if this is a Giro payment method
     */
    public function isGiro(): bool
    {
        return strtoupper($this->nama) === 'GIRO';
    }

    /**
     * Get bank code from payment method name (if applicable)
     */
    public function getBankCode(): ?string
    {
        // Extract bank code from nama field
        $nama = strtoupper($this->nama);
        
        if (strpos($nama, 'MANDIRI') !== false) {
            return '1104-1';
        } elseif (strpos($nama, 'BNI') !== false) {
            return '1104-2';
        } elseif (strpos($nama, 'BRI') !== false) {
            return '1104-3';
        } elseif (strpos($nama, 'BCA') !== false) {
            return '1104-4';
        }
        
        // Also check metode field for bank names
        $metode = strtoupper($this->metode);
        if (strpos($metode, 'MANDIRI') !== false) {
            return '1104-1';
        } elseif (strpos($metode, 'BNI') !== false) {
            return '1104-2';
        } elseif (strpos($metode, 'BRI') !== false) {
            return '1104-3';
        } elseif (strpos($metode, 'BCA') !== false) {
            return '1104-4';
        }
        
        return null;
    }

    /**
     * Get expected COA code for this payment method
     */
    public function getExpectedCoaCode(): ?string
    {
        if ($this->isCash()) {
            if (strpos(strtoupper($this->nama), 'KECIL') !== false) {
                return '1101'; // Kas Kecil
            } elseif (strpos(strtoupper($this->nama), 'BESAR') !== false) {
                return '1102'; // Kas Besar
            }
            return '1101'; // Default to Kas Kecil
        }

        $bankCode = $this->getBankCode();
        if ($bankCode) {
            if ($this->isQris()) {
                return $bankCode . '-QRIS';
            } elseif ($this->isEdc()) {
                return $bankCode . '-EDC';
            } elseif ($this->isGiro()) {
                return $bankCode . '-GIRO';
            } else {
                // If it's a bank but no specific payment method, return the bank code itself
                return $bankCode;
            }
        }

        return null;
    }
}
