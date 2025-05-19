<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KodeBarang extends Model
{
    /** @use HasFactory<\Database\Factories\KodeBarangFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'price',
        'cost',
        'kode_barang',
        'attribute',
        'length',
        'status',
        'kategori_id' // Add kategori_id to fillable
    ];

    protected $casts = [
        'length' => 'decimal:2',
        'cost' => 'decimal:2',
        'price' => 'decimal:2',
    ];

    public function getPanels()
    {
        return $this->hasMany(Panel::class);
    }

    // Add relationship to KategoriBarang
    public function kategori()
    {
        return $this->belongsTo(KategoriBarang::class, 'kategori_id');
    }
}