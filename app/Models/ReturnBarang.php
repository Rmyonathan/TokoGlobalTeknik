<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReturnBarang extends Model
{
    use HasFactory;

    protected $table = 'return_barang';

    protected $fillable = [
        'no_return',
        'tanggal',
        'kode_customer',
        'no_transaksi_asal',
        'tipe_return',
        'status',
        'alasan_return',
        'total_return',
        'created_by',
        'approved_by',
        'approved_at',
        'catatan_approval'
    ];

    protected $casts = [
        'tanggal' => 'date',
        'total_return' => 'decimal:2',
        'approved_at' => 'datetime',
    ];

    // Relasi dengan Customer
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'kode_customer', 'kode_customer');
    }

    // Relasi dengan Transaksi Asal
    public function transaksiAsal(): BelongsTo
    {
        return $this->belongsTo(Transaksi::class, 'no_transaksi_asal', 'no_transaksi');
    }

    // Relasi dengan Return Barang Items
    public function items(): HasMany
    {
        return $this->hasMany(ReturnBarangItem::class, 'return_barang_id');
    }

    // Scope untuk query yang sering digunakan
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    public function scopeProcessed($query)
    {
        return $query->where('status', 'processed');
    }

    public function scopeByCustomer($query, $customerId)
    {
        return $query->where('kode_customer', $customerId);
    }

    public function scopeByTipe($query, $tipe)
    {
        return $query->where('tipe_return', $tipe);
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('tanggal', [$startDate, $endDate]);
    }

    // Helper methods
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function isProcessed(): bool
    {
        return $this->status === 'processed';
    }

    public function canBeApproved(): bool
    {
        return $this->isPending();
    }

    public function canBeRejected(): bool
    {
        return $this->isPending();
    }

    public function canBeProcessed(): bool
    {
        return $this->isApproved();
    }

    // Generate nomor return otomatis
    public static function generateNoReturn(): string
    {
        $prefix = 'RET-';
        $date = now()->format('Ymd');

        // Ambil return terakhir hari ini
        $lastReturn = self::whereDate('tanggal', now()->toDateString())
            ->orderBy('no_return', 'desc')
            ->first();

        if ($lastReturn) {
            // ambil nomor urut terakhir
            $lastNumber = (int) substr($lastReturn->no_return, -3);
            $newNumber = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '001';
        }

        return $prefix . $date . '-' . $newNumber;
    }

    // Hitung total return dari items
    public function calculateTotalReturn(): float
    {
        return $this->items()->sum('total');
    }

    // Update total return
    public function updateTotalReturn(): void
    {
        $this->update(['total_return' => $this->calculateTotalReturn()]);
    }
}