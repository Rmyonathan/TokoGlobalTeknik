<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransaksiItemSumber extends Model
{
    use HasFactory;

    protected $table = 'transaksi_item_sumber';

    protected $fillable = [
        'transaksi_item_id',
        'stock_batch_id',
        'qty_diambil',
        'harga_modal',
        'surat_jalan_item_sumber_id'
    ];

    protected $casts = [
        'qty_diambil' => 'decimal:2',
        'harga_modal' => 'decimal:2',
    ];

    /**
     * Relasi ke TransaksiItem
     */
    public function transaksiItem(): BelongsTo
    {
        return $this->belongsTo(TransaksiItem::class, 'transaksi_item_id');
    }

    /**
     * Relasi ke StockBatch
     */
    public function stockBatch(): BelongsTo
    {
        return $this->belongsTo(StockBatch::class, 'stock_batch_id');
    }
}
