<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReturPenjualan extends Model
{
    protected $table = 'retur_penjualan';

    protected $fillable = [
        'no_retur',
        'tanggal',
        'kode_customer',
        'no_transaksi',
        'transaksi_id',
        'total_retur',
        'status',
        'alasan_retur',
        'created_by',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'total_retur' => 'decimal:2',
        'approved_at' => 'datetime',
    ];

    /**
     * Get the customer that owns the retur penjualan.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'kode_customer', 'kode_customer');
    }

    /**
     * Get the transaction that owns the retur penjualan.
     */
    public function transaksi(): BelongsTo
    {
        return $this->belongsTo(Transaksi::class, 'transaksi_id');
    }

    /**
     * Get the items for the retur penjualan.
     */
    public function items(): HasMany
    {
        return $this->hasMany(ReturPenjualanItem::class, 'retur_penjualan_id');
    }

    /**
     * Get the user who created the retur penjualan.
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who approved the retur penjualan.
     */
    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get the nota kredit for this retur penjualan.
     */
    public function notaKredit(): HasMany
    {
        return $this->hasMany(NotaKredit::class, 'retur_penjualan_id');
    }

    /**
     * Scope a query to only include pending retur penjualan.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope a query to only include approved retur penjualan.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope a query to only include processed retur penjualan.
     */
    public function scopeProcessed($query)
    {
        return $query->where('status', 'processed');
    }
}
