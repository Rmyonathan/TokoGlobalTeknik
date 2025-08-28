<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Wilayah extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama_wilayah',
        'keterangan',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Relasi dengan Customer
    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class, 'wilayah_id');
    }

    // Scope untuk query yang sering digunakan
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByNama($query, $nama)
    {
        return $query->where('nama_wilayah', 'like', "%{$nama}%");
    }
}
