<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SuratJalan extends Model{
    use HasFactory;

    protected $table = 'surat_jalan';

    protected $fillable = [
        'no_suratjalan',
        'tanggal',
        'kode_customer',
        'alamat_suratjalan',
        'no_transaksi',
        'tanggal_transaksi',
        'titipan_uang',
        'sisa_piutang'
    ];

    public function items()
    {
        return $this->hasMany(SuratJalanItem::class, 'no_suratjalan', 'no_suratjalan');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'kode_customer', 'kode_customer');
    }

    public function transaksi()
    {
        return $this->belongsTo(Transaksi::class, 'no_transaksi', 'no_transaksi');
    }
}