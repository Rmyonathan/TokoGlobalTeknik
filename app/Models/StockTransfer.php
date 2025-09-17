<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\MultiDatabaseTrait;

class StockTransfer extends Model
{
    use HasFactory, MultiDatabaseTrait;

    protected $fillable = [
        'no_transfer',
        'tanggal_transfer',
        'from_database',
        'to_database',
        'keterangan',
        'status', // pending, approved, completed, cancelled
        'approved_by',
        'approved_at',
        'created_by',
    ];

    protected $casts = [
        'tanggal_transfer' => 'date',
        'approved_at' => 'datetime',
    ];

    /**
     * Get the stock transfer items
     */
    public function items()
    {
        return $this->hasMany(StockTransferItem::class);
    }

    /**
     * Get the approver user
     */
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get the creator user
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope for pending transfers
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for approved transfers
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope for completed transfers
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Generate transfer number
     */
    public static function generateTransferNumber()
    {
        $lastTransfer = static::orderBy('id', 'desc')->first();
        $number = $lastTransfer ? (int) substr($lastTransfer->no_transfer, -5) + 1 : 1;
        return 'ST-' . date('Y') . '-' . str_pad($number, 5, '0', STR_PAD_LEFT);
    }
}
