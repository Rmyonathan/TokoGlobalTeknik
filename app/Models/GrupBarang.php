<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GrupBarang extends Model
{
    use HasFactory;

    protected $table = 'grup_barang';

    protected $fillable = [
        'name',
        'description',
        'status'
    ];

    // Relationship with KodeBarang - a grup barang can have many KodeBarang
    public function kodeBarang()
    {
        return $this->hasMany(KodeBarang::class, 'grup_barang_id');
    }
}
