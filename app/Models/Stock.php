<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Stock extends Model
{
    use HasFactory;

    protected $fillable = [
        'kode_barang',
        'nama_barang',
        'good_stock',
        'bad_stock',
        'so',
        'satuan',
    ];

    protected $casts = [
        'good_stock' => 'float',
        'bad_stock' => 'float',
    ];
}