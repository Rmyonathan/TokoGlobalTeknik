<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReturPenjualanItem extends Model
{
    protected $table = 'retur_penjualan_items';

    protected $fillable = [
        'retur_penjualan_id',
        'transaksi_item_id',
        'kode_barang',
        'nama_barang',
        'qty_retur',
        'satuan',
        'harga',
        'total',
        'alasan',
    ];

    protected $casts = [
        'qty_retur' => 'decimal:2',
        'harga' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    /**
     * Get the retur penjualan that owns the item.
     */
    public function returPenjualan(): BelongsTo
    {
        return $this->belongsTo(ReturPenjualan::class, 'retur_penjualan_id');
    }

    /**
     * Get the transaksi item that owns the retur item.
     */
    public function transaksiItem(): BelongsTo
    {
        return $this->belongsTo(TransaksiItem::class, 'transaksi_item_id');
    }

    /**
     * Get the kode barang that owns the retur item.
     */
    public function kodeBarang(): BelongsTo
    {
        return $this->belongsTo(KodeBarang::class, 'kode_barang', 'kode_barang');
    }
}
