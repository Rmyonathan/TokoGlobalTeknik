<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrderItem extends Model
{
    protected $table = 'purchase_order_items';

    protected $fillable = [
        'purchase_order_id', 'kode_barang', 'nama_barang',
        'keterangan', 'harga', 'panjang', 'qty', 'total', 'diskon',
    ];

    protected $casts = [
        'harga' => 'decimal:2',
        'qty' => 'decimal:2',
        'total' => 'decimal:2',
        'diskon' => 'decimal:2',
    ];

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }
}
