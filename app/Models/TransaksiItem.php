<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        'transaksi_id',
        'no_transaksi',
        'kode_barang',
        'nama_barang',
        'keterangan',
        'harga',
        'panjang',
        'lebar',
        'qty',
        'satuan',
        'diskon',
        'total',
        'ongkos_kuli',
    ];

    protected $casts = [
        'harga' => 'decimal:2',
        'qty' => 'decimal:2',
        'diskon' => 'decimal:2',
        'total' => 'decimal:2',
        'ongkos_kuli' => 'decimal:2',
    ];
    
    /**
     * Get the transaction that owns the item.
     */
    public function transaksi()
    {
        return $this->belongsTo(Transaksi::class, 'no_transaksi', 'no_transaksi');
    }

    public function itemsTransaksiId(){
        return $this->belongsTo(Transaksi::class, 'transaksi_id', 'id');
    }

    public function suratJalanItems()
    {
        return $this->hasMany(SuratJalanItem::class, 'transaksi_item_id', 'id');
    }

    /**
     * Relasi ke TransaksiItemSumber untuk tracking FIFO
     */
    public function transaksiItemSumber(): HasMany
    {
        return $this->hasMany(TransaksiItemSumber::class, 'transaksi_item_id');
    }
}

