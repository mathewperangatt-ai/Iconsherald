<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * User model — every person who logs in to IconsHerald.
 *
 * Authentication is Google OAuth only (password is nullable).
 * Mobile OTP verification is a mandatory second step tracked via mobile_verified_at.
 *
 * Roles: 'member' | 'admin' | 'editor'
 *   - member  → can view their own application status and profile
 *   - admin   → full access to the Filament editorial panel
 *   - editor  → (reserved for future trusted collaborators)
 */
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'google_id',
        'google_avatar',
        'mobile',
        'mobile_verified_at',
        'role',
        'is_founding_member',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'google_id',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at'    => 'datetime',
            'mobile_verified_at'   => 'datetime',
            'password'             => 'hashed',
            'is_founding_member'   => 'boolean',
        ];
    }

    // ── Role helpers ──────────────────────────────────────────────────────────

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isMember(): bool
    {
        return $this->role === 'member';
    }

    public function hasMobileVerified(): bool
    {
        return $this->mobile_verified_at !== null;
    }

    // ── Relationships ─────────────────────────────────────────────────────────

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }

    public function profile(): HasOne
    {
        return $this->hasOne(Profile::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function otpVerifications(): HasMany
    {
        return $this->hasMany(OtpVerification::class);
    }
}
