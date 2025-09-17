<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
     use HasFactory, Notifiable, HasRoles;


    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get user initials derived from the name.
     */
    public function getInitialsAttribute(): string
    {
        $name = trim((string) ($this->name ?? ''));
        if ($name === '') {
            return 'U';
        }

        // Take up to two initials from the name parts
        $parts = preg_split('/\s+/', $name, -1, PREG_SPLIT_NO_EMPTY);
        $initials = '';
        foreach ($parts as $idx => $part) {
            if ($idx > 1) break; // limit to 2 letters
            $initials .= mb_strtoupper(mb_substr($part, 0, 1));
        }

        return $initials !== '' ? $initials : 'U';
    }

    /**
     * Get a consistent avatar background color based on user's name/email.
     */
    public function getAvatarColorAttribute(): string
    {
        $seed = ($this->name ?: '') . '|' . ($this->email ?: '');
        if ($seed === '|') {
            return '#6c757d'; // fallback gray
        }

        // Create a deterministic color from hash
        $hash = md5($seed);
        $r = hexdec(substr($hash, 0, 2));
        $g = hexdec(substr($hash, 2, 2));
        $b = hexdec(substr($hash, 4, 2));

        // Soften colors for better UI (blend towards 200)
        $r = (int) round(($r + 200) / 2);
        $g = (int) round(($g + 200) / 2);
        $b = (int) round(($b + 200) / 2);

        return sprintf('#%02x%02x%02x', $r, $g, $b);
    }

}
