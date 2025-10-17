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

    /**
     * Check if account is a parent account (has children)
     */
    public function isParent(): bool
    {
        return $this->children()->exists();
    }

    /**
     * Check if account is a child account (has parent)
     */
    public function isChild(): bool
    {
        return !is_null($this->parent_id);
    }

    /**
     * Get all descendants (children, grandchildren, etc.)
     */
    public function descendants()
    {
        return $this->children()->with('descendants');
    }

    /**
     * Get all ancestors (parent, grandparent, etc.)
     */
    public function ancestors()
    {
        $ancestors = collect();
        $current = $this->parent;
        
        while ($current) {
            $ancestors->push($current);
            $current = $current->parent;
        }
        
        return $ancestors;
    }

    /**
     * Get account hierarchy path (e.g., "Bank > Bank Mandiri > Bank Mandiri - QRIS")
     */
    public function getHierarchyPathAttribute(): string
    {
        $path = collect();
        $current = $this;
        
        while ($current) {
            $path->prepend($current->name);
            $current = $current->parent;
        }
        
        return $path->implode(' > ');
    }

    /**
     * Calculate total balance including all child accounts
     */
    public function getTotalBalanceIncludingChildren(?int $periodId = null): float
    {
        $totalBalance = $this->calculateBalance($periodId);
        
        foreach ($this->children as $child) {
            $totalBalance += $child->getTotalBalanceIncludingChildren($periodId);
        }
        
        return $totalBalance;
    }

    /**
     * Scope for bank accounts
     */
    public function scopeBankAccounts($query)
    {
        return $query->where('code', 'like', '1104%');
    }

    /**
     * Scope for bank child accounts (QRIS, EDC, Giro)
     */
    public function scopeBankChildAccounts($query)
    {
        return $query->where('code', 'like', '%-QRIS')
            ->orWhere('code', 'like', '%-EDC')
            ->orWhere('code', 'like', '%-GIRO');
    }

    /**
     * Scope for QRIS accounts
     */
    public function scopeQrisAccounts($query)
    {
        return $query->where('code', 'like', '%-QRIS');
    }

    /**
     * Scope for EDC accounts
     */
    public function scopeEdcAccounts($query)
    {
        return $query->where('code', 'like', '%-EDC');
    }

    /**
     * Scope for Giro accounts
     */
    public function scopeGiroAccounts($query)
    {
        return $query->where('code', 'like', '%-GIRO');
    }
}
