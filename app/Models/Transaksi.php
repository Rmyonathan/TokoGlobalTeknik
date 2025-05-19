<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaksi extends Model
{
    use HasFactory;

    protected $table = 'transaksi';
    
    protected $fillable = [
        'no_transaksi',
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
        'created_from_po',
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
        return $this->hasMany(TransaksiItem::class, 'transaksi_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'kode_customer', 'kode_customer');
    }
}