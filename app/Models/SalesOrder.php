<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SalesOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'no_so',
        'tanggal',
        'customer_id',
        'salesman_id',
        'status',
        'subtotal',
        'diskon',
        'grand_total',
        'keterangan',
        'tanggal_estimasi',
        'cara_bayar',
        'hari_tempo',
        'tanggal_jatuh_tempo'
    ];

    protected $casts = [
        'tanggal' => 'date',
        'tanggal_estimasi' => 'date',
        'tanggal_jatuh_tempo' => 'date',
        'subtotal' => 'decimal:2',
        'diskon' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'hari_tempo' => 'integer',
    ];

    // Relasi dengan Customer
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    // Relasi dengan Salesman (StokOwner)
    public function salesman(): BelongsTo
    {
        return $this->belongsTo(StokOwner::class, 'salesman_id');
    }

    // Relasi dengan SalesOrderItem
    public function items(): HasMany
    {
        return $this->hasMany(SalesOrderItem::class, 'sales_order_id');
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

    public function scopeProcessed($query)
    {
        return $query->where('status', 'processed');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeCanceled($query)
    {
        return $query->where('status', 'canceled');
    }

    public function scopeByCustomer($query, $customerId)
    {
        return $query->where('customer_id', $customerId);
    }

    public function scopeBySalesman($query, $salesmanId)
    {
        return $query->where('salesman_id', $salesmanId);
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('tanggal', [$startDate, $endDate]);
    }

    // Helper methods
    public function getNamaSalesmanAttribute(): string
    {
        return optional($this->salesman)->keterangan ?? '-';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isProcessed(): bool
    {
        return $this->status === 'processed';
    }

    public function isCanceled(): bool
    {
        return $this->status === 'canceled';
    }

    public function canBeApproved(): bool
    {
        return $this->isPending() || $this->isCanceled();
    }

    public function canBeProcessed(): bool
    {
        return $this->isApproved() || $this->isCanceled();
    }

    public function canBeCanceled(): bool
    {
        return in_array($this->status, ['pending', 'approved']);
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function getStatusBadge(): string
    {
        $badges = [
            'pending' => 'badge-warning',
            'approved' => 'badge-info',
            'processed' => 'badge-success',
            'completed' => 'badge-primary',
            'canceled' => 'badge-danger'
        ];

        return $badges[$this->status] ?? 'badge-secondary';
    }

    public function getStatusText(): string
    {
        $texts = [
            'pending' => 'Menunggu Approval',
            'approved' => 'Disetujui',
            'processed' => 'Diproses',
            'completed' => 'Selesai',
            'canceled' => 'Dibatalkan'
        ];

        return $texts[$this->status] ?? 'Unknown';
    }

    // Hitung total quantity yang belum dikirim
    public function getTotalQtySisa(): float
    {
        return $this->items->sum('qty_sisa');
    }

    // Hitung total quantity yang sudah dikirim
    public function getTotalQtyTerkirim(): float
    {
        return $this->items->sum('qty_terkirim');
    }

    // Cek apakah semua item sudah dikirim
    public function isFullyShipped(): bool
    {
        return $this->getTotalQtySisa() == 0;
    }

    // Generate nomor SO otomatis
    public static function generateNoSo(): string
    {
        $lastSo = self::where('no_so', 'like', 'SO-' . date('Ymd') . '%')
            ->orderBy('no_so', 'desc')
            ->first();

        if ($lastSo) {
            $lastNumber = (int) substr($lastSo->no_so, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return 'SO-' . date('Ymd') . '-' . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }
}
