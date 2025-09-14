<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Journal extends Model
{
    protected $fillable = [
        'journal_no',
        'journal_date',
        'reference',
        'description',
        'accounting_period_id',
    ];

    public function period()
    {
        return $this->belongsTo(AccountingPeriod::class, 'accounting_period_id');
    }

    public function details()
    {
        return $this->hasMany(JournalDetail::class);
    }
}
