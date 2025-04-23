<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Panel extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'length',
        'price',
        'available',
        'parent_panel_id'
    ];

    protected $casts = [
        'length' => 'decimal:2',
        'price' => 'decimal:2',
        'available' => 'boolean',
    ];

    public function parentPanel()
    {
        return $this->belongsTo(Panel::class, 'parent_panel_id');
    }

    public function childPanels()
    {
        return $this->hasMany(Panel::class, 'parent_panel_id');
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function panel()
    {
        return $this->belongsTo(Panel::class, 'kode_barang', 'id');
    }
}
