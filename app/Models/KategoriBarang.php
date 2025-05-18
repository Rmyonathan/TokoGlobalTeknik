<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KategoriBarang extends Model
{
    use HasFactory;

    protected $table = 'kategori_barang';

    protected $fillable = [
        'name',
        'description',
        'status'
    ];

    // Relationship with KodeBarang - a category can have many KodeBarang
    public function kodeBarang()
    {
        return $this->hasMany(KodeBarang::class, 'kategori_id');
    }
}