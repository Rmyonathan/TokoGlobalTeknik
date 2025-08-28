<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StockBatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'kode_barang_id',
        'pembelian_item_id',
        'qty_masuk',
        'qty_sisa',
        'harga_beli',
        'tanggal_masuk',
        'batch_number',
        'keterangan'
    ];

    protected $casts = [
        'tanggal_masuk' => 'date',
        'qty_masuk' => 'decimal:2',
        'qty_sisa' => 'decimal:2',
        'harga_beli' => 'decimal:2',
    ];

    /**
     * Relasi ke KodeBarang
     */
    public function kodeBarang(): BelongsTo
    {
        return $this->belongsTo(KodeBarang::class, 'kode_barang_id');
    }

    /**
     * Relasi ke PembelianItem
     */
    public function pembelianItem(): BelongsTo
    {
        return $this->belongsTo(PembelianItem::class, 'pembelian_item_id');
    }

    /**
     * Relasi ke TransaksiItemSumber
     */
    public function transaksiItemSumber(): HasMany
    {
        return $this->hasMany(TransaksiItemSumber::class);
    }

    /**
     * Scope untuk batch yang masih memiliki stok tersisa
     */
    public function scopeTersisa($query)
    {
        return $query->where('qty_sisa', '>', 0);
    }

    /**
     * Scope untuk batch berdasarkan kode barang
     */
    public function scopeByKodeBarang($query, $kodeBarangId)
    {
        return $query->where('kode_barang_id', $kodeBarangId);
    }

    /**
     * Scope untuk batch FIFO (First In First Out)
     */
    public function scopeFifo($query)
    {
        return $query->orderBy('tanggal_masuk', 'asc');
    }
}
