<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'kode_customer', 
        'nama', 
        'alamat', 
        'hp', 
        'telepon',
        // Kolom baru untuk sistem kredit dan wilayah
        'limit_kredit',
        'sisa_kredit',
        'total_piutang',
        'limit_hari_tempo',
        'wilayah_id',
        'is_active'
    ];

    protected $casts = [
        'limit_kredit' => 'decimal:2',
        'sisa_kredit' => 'decimal:2',
        'total_piutang' => 'decimal:2',
        'limit_hari_tempo' => 'integer',
        'is_active' => 'boolean',
    ];

    // Relasi untuk sistem harga per customer
    public function customerPrices(): HasMany
    {
        return $this->hasMany(CustomerPrice::class, 'customer_id');
    }

    public function transaksi(): HasMany
    {
        return $this->hasMany(Transaksi::class, 'kode_customer', 'kode_customer');
    }

    // Relasi baru untuk sistem wilayah
    public function wilayah(): BelongsTo
    {
        return $this->belongsTo(Wilayah::class, 'wilayah_id');
    }

    // Scope untuk query yang sering digunakan
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByWilayah($query, $wilayahId)
    {
        return $query->where('wilayah_id', $wilayahId);
    }

    public function scopeByKredit($query, $minLimit = 0)
    {
        return $query->where('limit_kredit', '>=', $minLimit);
    }

    public function scopeByTempo($query, $hariTempo)
    {
        return $query->where('limit_hari_tempo', $hariTempo);
    }

    // Helper methods untuk sistem kredit
    public function isKredit(): bool
    {
        return $this->limit_hari_tempo > 0;
    }

    public function isTunai(): bool
    {
        return $this->limit_hari_tempo == 0;
    }

    public function getStatusKredit(): string
    {
        if ($this->isTunai()) {
            return 'Tunai';
        }
        return "Kredit ({$this->limit_hari_tempo} hari)";
    }

    public function getLimitKreditFormatted(): string
    {
        return 'Rp ' . number_format($this->limit_kredit, 0, ',', '.');
    }

    public function getSisaKreditFormatted(): string
    {
        return 'Rp ' . number_format($this->sisa_kredit, 0, ',', '.');
    }

    public function getTotalPiutangFormatted(): string
    {
        return 'Rp ' . number_format($this->total_piutang, 0, ',', '.');
    }

    /**
     * Update sisa kredit dan total piutang customer
     */
    public function updateCreditInfo()
    {
        // Hitung total piutang dari transaksi kredit yang belum lunas
        $totalPiutang = $this->transaksi()
            ->where('cara_bayar', 'Kredit')
            ->where('status_piutang', '!=', 'lunas')
            ->sum('sisa_piutang');

        // Hitung sisa kredit yang tersedia
        $sisaKredit = $this->limit_kredit - $totalPiutang;

        // Update data customer
        $this->update([
            'total_piutang' => $totalPiutang,
            'sisa_kredit' => max(0, $sisaKredit) // Pastikan tidak negatif
        ]);

        return [
            'total_piutang' => $totalPiutang,
            'sisa_kredit' => max(0, $sisaKredit),
            'limit_kredit' => $this->limit_kredit
        ];
    }

    /**
     * Cek apakah customer masih bisa melakukan transaksi kredit
     */
    public function canMakeCreditTransaction($amount): bool
    {
        $this->updateCreditInfo();
        return $this->sisa_kredit >= $amount;
    }
}
