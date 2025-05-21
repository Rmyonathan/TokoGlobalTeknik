<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    use HasFactory;

    protected $table = 'purchase_orders';

    protected $fillable = [
        'no_po',
        'tanggal',
        'kode_customer',
        'sales',
        'pembayaran',
        'cara_bayar',
        'tanggal_jadi',
        'subtotal',
        'discount',
        'disc_rupiah',
        'ppn',
        'dp',
        'grand_total',
        'status',
        'is_edited',
        'edited_by',
        'edited_at',
        'edit_reason',
    ];

    protected $casts = [
        'tanggal' => 'datetime',
        'tanggal_jadi' => 'datetime',
        'edited_at' => 'datetime',
        'is_edited' => 'boolean',
    ];

    public function items()
    {
        return $this->hasMany(PurchaseOrderItem::class, 'purchase_order_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'kode_customer', 'kode_customer');
    }
}