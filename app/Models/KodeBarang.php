<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KodeBarang extends Model
{
    /** @use HasFactory<\Database\Factories\KodeBarangFactory> */
    use HasFactory;

    protected $fillable = [
        'kode_barang',
        'attribute',
        'length'
    ];

    protected $casts = [
        'length' => 'decimal:2',
    ];

    public function getPanels()
    {
        return $this->hasMany(Panel::class);
    }
}