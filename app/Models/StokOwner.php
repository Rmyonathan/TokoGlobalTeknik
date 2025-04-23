<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StokOwner extends Model
{
    use HasFactory;

    protected $fillable = [
        'kode_stok_owner',
        'keterangan',
        'default',
    ];

    public function transaksi()
    {
        return $this->hasMany(Transaksi::class, 'sales', 'kode_stok_owner');
    }

}