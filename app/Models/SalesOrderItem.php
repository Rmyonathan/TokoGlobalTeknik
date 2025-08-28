<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalesOrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'sales_order_id',
        'kode_barang_id',
        'qty',
        'satuan',
        'harga',
        'total',
        'qty_terkirim',
        'qty_sisa',
        'keterangan'
    ];

    protected $casts = [
        'qty' => 'decimal:2',
        'harga' => 'decimal:2',
        'total' => 'decimal:2',
        'qty_terkirim' => 'decimal:2',
        'qty_sisa' => 'decimal:2',
    ];

    // Relasi dengan SalesOrder
    public function salesOrder(): BelongsTo
    {
        return $this->belongsTo(SalesOrder::class, 'sales_order_id');
    }

    // Relasi dengan KodeBarang
    public function kodeBarang(): BelongsTo
    {
        return $this->belongsTo(KodeBarang::class, 'kode_barang_id');
    }

    // Scope untuk query yang sering digunakan
    public function scopeBySalesOrder($query, $salesOrderId)
    {
        return $query->where('sales_order_id', $salesOrderId);
    }

    public function scopeByKodeBarang($query, $kodeBarangId)
    {
        return $query->where('kode_barang_id', $kodeBarangId);
    }

    public function scopeBySatuan($query, $satuan)
    {
        return $query->where('satuan', $satuan);
    }

    public function scopeBelumTerkirim($query)
    {
        return $query->where('qty_sisa', '>', 0);
    }

    public function scopeSudahTerkirim($query)
    {
        return $query->where('qty_terkirim', '>', 0);
    }

    // Helper methods
    public function isFullyShipped(): bool
    {
        return $this->qty_sisa == 0;
    }

    public function isPartiallyShipped(): bool
    {
        return $this->qty_terkirim > 0 && $this->qty_sisa > 0;
    }

    public function isNotShipped(): bool
    {
        return $this->qty_terkirim == 0;
    }

    public function getShippedPercentage(): float
    {
        if ($this->qty == 0) return 0;
        return ($this->qty_terkirim / $this->qty) * 100;
    }

    public function getShippedStatus(): string
    {
        if ($this->isFullyShipped()) {
            return 'Lengkap';
        } elseif ($this->isPartiallyShipped()) {
            return 'Sebagian';
        } else {
            return 'Belum';
        }
    }

    public function getShippedBadge(): string
    {
        if ($this->isFullyShipped()) {
            return 'badge-success';
        } elseif ($this->isPartiallyShipped()) {
            return 'badge-warning';
        } else {
            return 'badge-secondary';
        }
    }

    // Update qty_sisa berdasarkan qty_terkirim
    public function updateQtySisa(): void
    {
        $this->qty_sisa = $this->qty - $this->qty_terkirim;
        $this->save();
    }

    // Tambah qty_terkirim dan update qty_sisa
    public function addQtyTerkirim(float $qty): void
    {
        $this->qty_terkirim += $qty;
        $this->updateQtySisa();
    }

    // Boot method untuk auto-calculate total
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($item) {
            $item->total = $item->qty * $item->harga;
            $item->qty_sisa = $item->qty;
        });

        static::updating(function ($item) {
            if ($item->isDirty(['qty', 'harga'])) {
                $item->total = $item->qty * $item->harga;
            }
            if ($item->isDirty(['qty', 'qty_terkirim'])) {
                $item->qty_sisa = $item->qty - $item->qty_terkirim;
            }
        });
    }
}
