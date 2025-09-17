<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\MultiDatabaseTrait;

class StockTransferItem extends Model
{
    use HasFactory, MultiDatabaseTrait;

    protected $fillable = [
        'stock_transfer_id',
        'kode_barang',
        'nama_barang',
        'qty_transfer',
        'satuan',
        'harga_per_unit',
        'total_value',
        'keterangan',
    ];

    protected $casts = [
        'qty_transfer' => 'decimal:2',
        'harga_per_unit' => 'decimal:2',
        'total_value' => 'decimal:2',
    ];

    /**
     * Get the parent stock transfer
     */
    public function stockTransfer()
    {
        return $this->belongsTo(StockTransfer::class);
    }

    /**
     * Get the product information
     */
    public function product()
    {
        return $this->belongsTo(KodeBarang::class, 'kode_barang', 'kode_barang');
    }
}
