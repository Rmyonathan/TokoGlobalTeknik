<?php

namespace App\Models;

use Spatie\Permission\Models\Role as SpatieRole;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Role extends SpatieRole
{
    protected $fillable = [
        'name',
        'display_name',
        'description',
        'group_id',
        'sort_order',
        'is_active',
        'guard_name',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Get the role group that owns the role
     */
    public function group(): BelongsTo
    {
        return $this->belongsTo(RoleGroup::class, 'group_id');
    }

    /**
     * Scope for active roles
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
        return $query->orderBy('sort_order')->orderBy('name');
    }

    /**
     * Get formatted display name
     */
    public function getFormattedDisplayNameAttribute(): string
    {
        return $this->display_name ?: $this->name;
    }

    /**
     * Get role group color
     */
    public function getGroupColorAttribute(): string
    {
        return $this->group ? $this->group->formatted_color : '#6c757d';
    }

    /**
     * Get role group icon
     */
    public function getGroupIconAttribute(): string
    {
        return $this->group ? $this->group->formatted_icon : 'fas fa-user-tag';
    }
}
