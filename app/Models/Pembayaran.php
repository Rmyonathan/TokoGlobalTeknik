<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Pembayaran extends Model
{
    use HasFactory;

    protected $table = 'pembayarans';

    protected $fillable = [
        'customer_id',
        'no_pembayaran',
        'tanggal_bayar',
        'total_bayar',
        'total_piutang',
        'sisa_piutang',
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
        'total_piutang' => 'decimal:2',
        'sisa_piutang' => 'decimal:2',
        'confirmed_at' => 'datetime',
    ];

    // Relationships
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function details(): HasMany
    {
        return $this->hasMany(PembayaranDetail::class, 'pembayaran_id');
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

    public function scopeByCustomer($query, $customerId)
    {
        return $query->where('customer_id', $customerId);
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('tanggal_bayar', [$startDate, $endDate]);
    }

    // Business Logic Methods
    public function isConfirmed(): bool
    {
        return $this->status === 'confirmed';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    public function confirm($userId): bool
    {
        $this->update([
            'status' => 'confirmed',
            'confirmed_by' => $userId,
            'confirmed_at' => now()
        ]);

        return true;
    }

    public function cancel($userId): bool
    {
        $this->update([
            'status' => 'cancelled'
        ]);

        return true;
    }

    public function getTotalPelunasanAttribute(): float
    {
        return $this->details->sum('jumlah_dilunasi');
    }

    public function getEfisiensiPelunasanAttribute(): float
    {
        if ($this->total_piutang == 0) return 0;
        return ($this->total_bayar / $this->total_piutang) * 100;
    }

    // Static Methods
    public static function generateNoPembayaran(): string
    {
        $prefix = 'PAY';
        $date = now()->format('Ymd');
        $lastPayment = self::where('no_pembayaran', 'like', $prefix . $date . '%')
            ->orderBy('no_pembayaran', 'desc')
            ->first();

        if ($lastPayment) {
            $lastNumber = (int) substr($lastPayment->no_pembayaran, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . $date . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
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

    public static function getLaporanPembayaranPerCustomer($startDate = null, $endDate = null)
    {
        $query = self::with('customer')
            ->where('status', 'confirmed');

        if ($startDate && $endDate) {
            $query->byDateRange($startDate, $endDate);
        }

        return $query->get()
            ->groupBy('customer_id')
            ->map(function ($payments, $customerId) {
                $customer = $payments->first()->customer;
                return [
                    'customer_id' => $customerId,
                    'nama_customer' => $customer->nama,
                    'total_pembayaran' => $payments->sum('total_bayar'),
                    'jumlah_transaksi' => $payments->count(),
                    'rata_rata_pembayaran' => $payments->avg('total_bayar'),
                    'pembayaran_terakhir' => $payments->max('tanggal_bayar')
                ];
            })
            ->values();
    }
}
