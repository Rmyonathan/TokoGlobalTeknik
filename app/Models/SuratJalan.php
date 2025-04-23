<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SuratJalan extends Model
{
    use HasFactory;

    protected $table = 'surat_jalan';

    protected $fillable = [
        'no_suratjalan',
        'tanggal',
        'customer_id',
        'alamat',
        'alamat_suratjalan',
        'no_transaksi',
        'tanggal_transaksi',
    ];

    public function items()
    {
        return $this->hasMany(SuratJalanItem::class, 'surat_jalan_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function transaksi()
    {
        return $this->belongsTo(Transaksi::class, 'no_transaksi', 'no_transaksi');
    }

    public function getStatusBarangAttribute()
    {
        $totalQty = $this->items->sum(function ($item) {
            return $item->transaksiItem->qty;
        });
        $totalDibawa = $this->items->sum('qty_dibawa');

        return $totalDibawa >= $totalQty ? 'Selesai' : 'Belum Selesai';
    }
}