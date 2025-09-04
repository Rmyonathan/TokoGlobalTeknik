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
        'transaction'
    ];

    protected $casts = [
        'transaction' => 'decimal:2',
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