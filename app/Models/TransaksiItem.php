<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransaksiItem extends Model
{
    use HasFactory;
    
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'transaksi_items';
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'no_transaksi',
        'kode_barang',
        'nama_barang',
        'keterangan',
        'harga',
        'panjang',
        'lebar',
        'qty',
        'diskon',
        'total',
    ];
    
    /**
     * Get the transaction that owns the item.
     */
    public function transaksi()
    {
        return $this->belongsTo(Transaksi::class, 'no_transaksi', 'no_transaksi');
    }
}