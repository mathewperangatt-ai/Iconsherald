<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Hash;

/**
 * OtpVerification model — a short-lived record holding a hashed OTP code.
 *
 * The raw OTP code is NEVER stored. Only the bcrypt hash is persisted.
 * Each row expires after 10 minutes and is deleted once used.
 * After 5 failed attempts the record is locked to prevent brute-force guessing.
 */
class OtpVerification extends Model
{
    protected $fillable = [
        'user_id',
        'mobile',
        'code_hash',
        'expires_at',
        'attempts',
        'verified',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'verified'   => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function isLocked(): bool
    {
        return $this->attempts >= 5;
    }

    public function checkCode(string $rawCode): bool
    {
        return Hash::check($rawCode, $this->code_hash);
    }
}
