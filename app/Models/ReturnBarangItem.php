<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReturnBarangItem extends Model
{
    use HasFactory;

    protected $table = 'return_barang_items';

    protected $fillable = [
        'return_barang_id',
        'kode_barang',
        'nama_barang',
        'keterangan',
        'qty_return',
        'satuan',
        'harga',
        'total',
        'status_item',
        'catatan_item'
    ];

    protected $casts = [
        'qty_return' => 'decimal:2',
        'harga' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    // Relasi dengan Return Barang
    public function returnBarang(): BelongsTo
    {
        return $this->belongsTo(ReturnBarang::class, 'return_barang_id');
    }

    // Relasi dengan Kode Barang
    public function kodeBarang(): BelongsTo
    {
        return $this->belongsTo(KodeBarang::class, 'kode_barang', 'kode_barang');
    }

    // Scope untuk query yang sering digunakan
    public function scopePending($query)
    {
        return $query->where('status_item', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status_item', 'approved');
    }

    public function scopeRejected($query)
    {
        return $query->where('status_item', 'rejected');
    }

    // Helper methods
    public function isPending(): bool
    {
        return $this->status_item === 'pending';
    }

    public function isApproved(): bool
    {
        return $this->status_item === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->status_item === 'rejected';
    }

    public function canBeApproved(): bool
    {
        return $this->isPending();
    }

    public function canBeRejected(): bool
    {
        return $this->isPending();
    }

    // Hitung total item
    public function calculateTotal(): float
    {
        return $this->qty_return * $this->harga;
    }

    // Update total item
    public function updateTotal(): void
    {
        $this->update(['total' => $this->calculateTotal()]);
    }
}