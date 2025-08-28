<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerPrice extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'kode_barang_id',
        'harga_jual_khusus',
        'ongkos_kuli_khusus',
        'unit_jual',
        'is_active',
        'keterangan'
    ];

    protected $casts = [
        'harga_jual_khusus' => 'decimal:2',
        'ongkos_kuli_khusus' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function kodeBarang(): BelongsTo
    {
        return $this->belongsTo(KodeBarang::class, 'kode_barang_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByCustomer($query, $customerId)
    {
        return $query->where('customer_id', $customerId);
    }

    public function scopeByKodeBarang($query, $kodeBarangId)
    {
        return $query->where('kode_barang_id', $kodeBarangId);
    }
}
