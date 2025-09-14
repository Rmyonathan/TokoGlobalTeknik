<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SuratJalanItem extends Model{
    use HasFactory;

    protected $table = 'surat_jalan_items';

    protected $fillable = [
        'no_suratjalan',
        'transaksi_id',
        'kode_barang',
        'nama_barang',
        'qty',
        'satuan',
        'satuan_besar',
    ];

    public function suratJalan()
    {
        return $this->belongsTo(SuratJalan::class, 'no_suratjalan', 'no_suratjalan');
    }

    public function transaksiItem()
    {
        return $this->hasMany(TransaksiItem::class, 'transaksi_id', 'transaksi_id');
    }

    // Relasi dengan SuratJalanItemSumber untuk tracking FIFO
    public function suratJalanItemSumber(): HasMany
    {
        return $this->hasMany(SuratJalanItemSumber::class, 'surat_jalan_item_id');
    }
}