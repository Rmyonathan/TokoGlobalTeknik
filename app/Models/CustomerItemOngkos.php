<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerItemOngkos extends Model
{
    use HasFactory;

    protected $table = 'customer_item_ongkos';

    protected $fillable = [
        'customer_id',
        'kode_barang_id',
        'ongkos_kuli_khusus',
        'keterangan',
        'is_active'
    ];

    protected $casts = [
        'ongkos_kuli_khusus' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    // Relasi dengan Customer
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    // Relasi dengan KodeBarang
    public function kodeBarang(): BelongsTo
    {
        return $this->belongsTo(KodeBarang::class, 'kode_barang_id');
    }

    // Scope untuk query yang sering digunakan
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

    public function scopeByCustomerAndBarang($query, $customerId, $kodeBarangId)
    {
        return $query->where('customer_id', $customerId)
                    ->where('kode_barang_id', $kodeBarangId);
    }

    // Helper methods
    public function isActive(): bool
    {
        return $this->is_active;
    }

    public function getFormattedOngkosKuli(): string
    {
        return 'Rp ' . number_format($this->ongkos_kuli_khusus, 0, ',', '.');
    }

    // Static method untuk mencari atau membuat record
    public static function getOngkosKuli(int $customerId, int $kodeBarangId): ?float
    {
        $record = self::active()
                     ->byCustomerAndBarang($customerId, $kodeBarangId)
                     ->first();

        return $record ? $record->ongkos_kuli_khusus : null;
    }

    // Static method untuk update atau create
    public static function updateOrCreateOngkos(int $customerId, int $kodeBarangId, float $ongkosKuli, ?string $keterangan = null): self
    {
        return self::updateOrCreate(
            [
                'customer_id' => $customerId,
                'kode_barang_id' => $kodeBarangId,
            ],
            [
                'ongkos_kuli_khusus' => $ongkosKuli,
                'keterangan' => $keterangan,
                'is_active' => true,
            ]
        );
    }
}