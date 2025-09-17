<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockTransferLog extends Model
{
    protected $fillable = [
        'transfer_no',
        'kode_barang',
        'kode_barang_id',
        'qty',
        'avg_cost',
        'unit',
        'source_db',
        'target_db',
        'role',
        'created_by',
        'note',
    ];

    protected $casts = [
        'qty' => 'decimal:2',
        'avg_cost' => 'decimal:2',
    ];
}
