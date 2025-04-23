<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'panel_id',
        'name',
        'length',
        'transaction',
        'original_panel_length',
        'remaining_length'
    ];

    protected $casts = [
        'length' => 'decimal:2',
        'transaction' => 'decimal:2',
        'original_panel_length' => 'decimal:2',
        'remaining_length' => 'decimal:2',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function panel()
    {
        return $this->belongsTo(Panel::class);
    }
}