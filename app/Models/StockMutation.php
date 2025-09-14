<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockMutation extends Model
{
    use HasFactory;

    protected $fillable = [
        'kode_barang',
        'nama_barang',
        'no_transaksi',
        'tanggal',
        'no_nota',
        'supplier_customer',
        'plus',
        'minus',
        'total',
        'so',
        'satuan',
        'keterangan',
        'created_by'

    ];

    protected $casts = [
        'tanggal' => 'datetime',
        'plus' => 'float',
        'minus' => 'float',
        'total' => 'float',
    ];

    /**
     * Get the kode barang that owns the stock mutation.
     */
    public function kodeBarang()
    {
        return $this->belongsTo(KodeBarang::class, 'kode_barang', 'kode_barang');
    }
}