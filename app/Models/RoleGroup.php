<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Permission\Models\Role;

class RoleGroup extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'display_name',
        'description',
        'color',
        'icon',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Get roles that belong to this group
     */
    public function roles(): HasMany
    {
        return $this->hasMany(Role::class, 'group_id');
    }

    /**
     * Scope for active role groups
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for ordering by sort order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('display_name');
    }

    /**
     * Get the role group with its roles
     */
    public function scopeWithRoles($query)
    {
        return $query->with(['roles' => function ($query) {
            $query->orderBy('name');
        }]);
    }

    /**
     * Get formatted color for CSS
     */
    public function getFormattedColorAttribute(): string
    {
        return $this->color ?: '#6c757d';
    }

    /**
     * Get formatted icon for display
     */
    public function getFormattedIconAttribute(): string
    {
        return $this->icon ?: 'fas fa-users';
    }

    /**
     * Get role count for this group
     */
    public function getRoleCountAttribute(): int
    {
        return $this->roles()->count();
    }

    /**
     * Get user count for this group (sum of all roles in group)
     */
    public function getUserCountAttribute(): int
    {
        return $this->roles()->withCount('users')->get()->sum('users_count');
    }
}