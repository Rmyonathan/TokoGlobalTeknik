<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'total_quantity',
        'total_length',
        'transaction',
        'status'
    ];

    protected $casts = [
        'total_quantity' => 'integer',
        'price' => 'decimal:2',
        'total_length' => 'decimal:2',
    ];

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function panels()
    {
        return $this->hasManyThrough(Panel::class, OrderItem::class, 'order_id', 'id', 'id', 'panel_id');
    }
}
