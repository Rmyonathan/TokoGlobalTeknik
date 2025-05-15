<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaksi extends Model
{
    use HasFactory;
    
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'transaksi';
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
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
    ];
    
    /**
     * Get the items for the transaction.
     */
    public function items()
    {
        return $this->hasMany(TransaksiItem::class, 'no_transaksi', 'no_transaksi');
    }

    public function itemsTransaksiId(){
        return $this->hasMany(TransaksiItem::class, 'transaksi_id', 'id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'kode_customer', 'kode_customer');
    }

    public function stokOwner()
    {
        return $this->belongsTo(StokOwner::class, 'sales', 'kode_stok_owner');
    }
    
}