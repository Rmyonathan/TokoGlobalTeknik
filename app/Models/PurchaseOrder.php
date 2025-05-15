<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    protected $table = 'purchase_orders';

    protected $fillable = [
        'no_po', 'tanggal', 'kode_customer', 'sales',
        'pembayaran', 'cara_bayar', 'tanggal_jadi',
        'subtotal', 'discount', 'disc_rupiah', 'ppn',
        'dp', 'grand_total', 'status',
    ];

    public function items()
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'kode_customer', 'kode_customer');
    }

}
