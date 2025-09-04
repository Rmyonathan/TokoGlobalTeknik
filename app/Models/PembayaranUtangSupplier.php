<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class PembayaranUtangSupplier extends Model
{
    use HasFactory;

    protected $table = 'pembayaran_utang_suppliers';

    protected $fillable = [
        'supplier_id',
        'no_pembayaran',
        'tanggal_bayar',
        'total_bayar',
        'total_utang',
        'sisa_utang',
        'total_nota_debit',
        'metode_pembayaran',
        'cara_bayar',
        'no_referensi',
        'keterangan',
        'status',
        'created_by',
        'confirmed_by',
        'confirmed_at'
    ];

    protected $casts = [
        'tanggal_bayar' => 'date',
        'total_bayar' => 'decimal:2',
        'total_utang' => 'decimal:2',
        'sisa_utang' => 'decimal:2',
        'total_nota_debit' => 'decimal:2',
        'confirmed_at' => 'datetime',
    ];

    // Relationships
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function details(): HasMany
    {
        return $this->hasMany(PembayaranUtangSupplierDetail::class, 'pembayaran_utang_supplier_id');
    }

    public function notaDebits(): HasMany
    {
        return $this->hasMany(PembayaranUtangSupplierNotaDebit::class, 'pembayaran_utang_supplier_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function confirmedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeConfirmed($query)
    {
        return $query->where('status', 'confirmed');
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    public function scopeBySupplier($query, $supplierId)
    {
        return $query->where('supplier_id', $supplierId);
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('tanggal_bayar', [$startDate, $endDate]);
    }

    // Static methods
    public static function generateNoPembayaran(): string
    {
        $year = date('Y');
        $month = date('m');
        
        $lastPayment = self::whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->orderBy('id', 'desc')
            ->first();
        
        if ($lastPayment) {
            $lastNumber = (int) substr($lastPayment->no_pembayaran, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        
        return "PUS-{$year}{$month}-" . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    public static function getTotalPembayaranHariIni(): float
    {
        return self::whereDate('tanggal_bayar', today())
            ->where('status', 'confirmed')
            ->sum('total_bayar');
    }

    public static function getTotalPembayaranBulanIni(): float
    {
        return self::whereYear('tanggal_bayar', now()->year)
            ->whereMonth('tanggal_bayar', now()->month)
            ->where('status', 'confirmed')
            ->sum('total_bayar');
    }
}
