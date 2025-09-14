<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\GrupBarang;

class KodeBarang extends Model
{
    /** @use HasFactory<\Database\Factories\KodeBarangFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'cost',
        'kode_barang',
        'attribute',
        'merek',
        'ukuran',
        // 'length',
        'status',
        // Kolom yang sudah ada di database
        'grup_barang_id',
        'unit_dasar',
        'nilai_konversi',
        'satuan_dasar',
        'satuan_besar',
        'harga_jual',
        'ongkos_kuli_default',
    ];

    protected $casts = [
        // 'length' => 'decimal:2',
        'cost' => 'decimal:2',
        'harga_jual' => 'decimal:2',
        'ongkos_kuli_default' => 'decimal:2',
    ];

    public function getPanels()
    {
        return $this->hasMany(Panel::class);
    }

    // Add relationship to GrupBarang using existing grup_barang_id column
    public function grupBarang()
    {
        return $this->belongsTo(GrupBarang::class, 'grup_barang_id');
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

    public function scopeByGrupBarang($query, $grupBarangId)
    {
        return $query->where('grup_barang_id', $grupBarangId);
    }

    public function scopeByUnitDasar($query, $unit)
    {
        return $query->where('unit_dasar', $unit);
    }
}