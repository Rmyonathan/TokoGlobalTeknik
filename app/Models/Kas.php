<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kas extends Model
{
    protected $fillable = [
        'name',
        'description',
        'qty',
        'transaction',
        'saldo',
        'type',
        'is_canceled',
        'is_manual',
    ];
}