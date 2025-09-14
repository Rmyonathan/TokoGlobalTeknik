<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\MultiDatabaseTrait;

class ChartOfAccount extends Model
{
    use MultiDatabaseTrait;

    protected $fillable = [
        'code',
        'name',
        'account_type_id',
        'parent_id',
        'is_active',
        'balance',
        'balance_updated_at',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
        'balance_updated_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function type()
    {
        return $this->belongsTo(AccountType::class, 'account_type_id');
    }

    public function accountType()
    {
        return $this->belongsTo(AccountType::class, 'account_type_id');
    }

    public function parent()
    {
        return $this->belongsTo(ChartOfAccount::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(ChartOfAccount::class, 'parent_id');
    }

    public function journalDetails()
    {
        return $this->hasMany(JournalDetail::class, 'account_id');
    }

    /**
     * Calculate account balance from journal entries
     */
    public function calculateBalance(?int $periodId = null): float
    {
        $query = $this->journalDetails();
        
        if ($periodId) {
            $query->whereHas('journal', function($q) use ($periodId) {
                $q->where('accounting_period_id', $periodId);
            });
        }
        
        $debitSum = (float) $query->sum('debit');
        $creditSum = (float) $query->sum('credit');
        
        // For Asset and Expense accounts: Debit - Credit
        // For Liability, Equity, and Revenue accounts: Credit - Debit
        $accountType = $this->accountType;
        if ($accountType && in_array($accountType->name, ['Asset', 'Expense'])) {
            return $debitSum - $creditSum;
        } else {
            return $creditSum - $debitSum;
        }
    }

    /**
     * Update account balance
     */
    public function updateBalance(?int $periodId = null): bool
    {
        $balance = $this->calculateBalance($periodId);
        return $this->update([
            'balance' => $balance,
            'balance_updated_at' => now()
        ]);
    }

    /**
     * Get formatted balance
     */
    public function getFormattedBalanceAttribute(): string
    {
        return number_format($this->balance, 2);
    }

    /**
     * Get balance with currency symbol
     */
    public function getBalanceWithCurrencyAttribute(): string
    {
        return 'Rp ' . number_format($this->balance, 2, ',', '.');
    }

    /**
     * Check if account has positive balance
     */
    public function hasPositiveBalance(): bool
    {
        return $this->balance > 0;
    }

    /**
     * Check if account has negative balance
     */
    public function hasNegativeBalance(): bool
    {
        return $this->balance < 0;
    }
}
