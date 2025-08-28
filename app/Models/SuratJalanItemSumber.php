<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SuratJalanItemSumber extends Model
{
    use HasFactory;

    protected $table = 'surat_jalan_item_sumber';

    protected $fillable = [
        'surat_jalan_item_id',
        'stock_batch_id',
        'qty_diambil',
        'harga_modal'
    ];

    protected $casts = [
        'qty_diambil' => 'decimal:2',
        'harga_modal' => 'decimal:2',
    ];

    public function suratJalanItem(): BelongsTo
    {
        return $this->belongsTo(SuratJalanItem::class, 'surat_jalan_item_id');
    }

    public function stockBatch(): BelongsTo
    {
        return $this->belongsTo(StockBatch::class, 'stock_batch_id');
    }
}
