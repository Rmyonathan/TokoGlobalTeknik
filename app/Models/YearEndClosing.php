<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class YearEndClosing extends Model
{
    protected $fillable = [
        'accounting_period_id',
        'fiscal_year',
        'closed_on',
        'closed_by',
        'status',
        'metadata',
        'snapshots',
    ];

    protected $casts = [
        'closed_on' => 'date',
        'metadata' => 'array',
        'snapshots' => 'array',
    ];

    public function period()
    {
        return $this->belongsTo(AccountingPeriod::class, 'accounting_period_id');
    }
}
