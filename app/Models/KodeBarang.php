<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        // 'length',
        'status',
        // Kolom baru untuk sistem unit dan harga
        'kategori_barang_id',
        'unit_dasar',
        'harga_jual',
        'ongkos_kuli_default',
    ];

    protected $casts = [
        // 'length' => 'decimal:2',
        'cost' => 'decimal:2',
        'price' => 'decimal:2',
        'harga_jual' => 'decimal:2',
        'ongkos_kuli_default' => 'decimal:2',
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

    // Relasi baru untuk sistem unit dan harga
    public function kategoriBarang(): BelongsTo
    {
        return $this->belongsTo(KategoriBarang::class, 'kategori_barang_id');
    }

    public function unitConversions(): HasMany
    {
        return $this->hasMany(UnitConversion::class, 'kode_barang_id');
    }

    public function customerPrices(): HasMany
    {
        return $this->hasMany(CustomerPrice::class, 'kode_barang_id');
    }

    public function stockBatches(): HasMany
    {
        return $this->hasMany(StockBatch::class, 'kode_barang_id');
    }

    // Scope untuk query yang sering digunakan
    public function scopeActive($query)
    {
        return $query->where('status', 'Active');
    }

    public function scopeByKategori($query, $kategoriId)
    {
        return $query->where('kategori_barang_id', $kategoriId);
    }

    public function scopeByUnitDasar($query, $unit)
    {
        return $query->where('unit_dasar', $unit);
    }
}