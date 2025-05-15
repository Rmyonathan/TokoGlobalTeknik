<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockAdjustment extends Model
{
    use HasFactory;

    protected $fillable = [
        'stock_id',
        'kode_barang',
        'quantity_before',
        'quantity_after',
        'difference',
        'keterangan',
        'user_id'
    ];

    /**
     * Get the stock that was adjusted.
     */
    public function stock()
    {
        return $this->belongsTo(Stock::class);
    }

    /**
     * Get the user who made the adjustment.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}