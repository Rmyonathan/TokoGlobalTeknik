<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UnitConversion extends Model
{
    use HasFactory;

    protected $fillable = [
        'kode_barang_id',
        'unit_turunan',
        'nilai_konversi',
        'keterangan',
        'is_active'
    ];

    protected $casts = [
        'nilai_konversi' => 'decimal:2',  // FIX: Gunakan decimal untuk desimal
        'is_active' => 'boolean',
    ];

    public function kodeBarang(): BelongsTo
    {
        return $this->belongsTo(KodeBarang::class, 'kode_barang_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByKodeBarang($query, $kodeBarangId)
    {
        return $query->where('kode_barang_id', $kodeBarangId);
    }

    public function scopeByUnit($query, $unit)
    {
        return $query->where('unit_turunan', $unit);
    }
}
