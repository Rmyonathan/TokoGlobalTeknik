<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SuratJalanItem extends Model
{
    use HasFactory;

    protected $table = 'surat_jalan_items';

    protected $fillable = [
        'surat_jalan_id',
        'transaksi_item_id',
        'qty_dibawa',
    ];

    public function suratJalan()
    {
        return $this->belongsTo(SuratJalan::class, 'surat_jalan_id', 'id');
    }

    public function transaksiItem()
    {
        return $this->belongsTo(TransaksiItem::class, 'transaksi_item_id', 'id');
    }
}