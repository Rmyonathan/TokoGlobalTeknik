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
    ];

    protected $casts = [
        'tanggal' => 'datetime',
        'plus' => 'float',
        'minus' => 'float',
        'total' => 'float',
    ];
}