<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * LegacyProfile model — an In Memoriam memorial archive entry.
 *
 * NEVER mixed with the Profile (Register) model. The Constitution mandates
 * complete architectural separation between living professionals and memorial content.
 *
 * All In Memoriam commissions are admin-initiated; there is no self-service path.
 * Three mandatory verification documents must be on file before any editorial
 * work or payment request can proceed (enforced in the commissioning workflow).
 *
 * Profiles are 'sealed' on publication — content cannot be changed without a
 * formal Major Editorial Revision request (₹5,000 + GST).
 */
class LegacyProfile extends Model
{
    protected $fillable = [
        'slug',
        'full_name',
        'credentials',
        'designation',
        'organisation',
        'location',
        'profession',
        'date_of_birth',
        'date_of_death',
        'commissioned_by_name',
        'commissioned_by_org',
        'commissioned_by_display_preference',
        'commissioned_by_display_value',
        'death_certificate_path',
        'commissioner_id_path',
        'commissioner_relationship',
        'documents_verified_at',
        'biography',
        'excerpt',
        'portrait_path',
        'gallery_paths',
        'career_timeline',
        'awards',
        'use_sepia_treatment',
        'draft_status',
        'ai_generated_at',
        'editor_reviewed_at',
        'sealed_at',
        'included_until',
        'active_until',
        'admin_notes',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth'         => 'date',
            'date_of_death'         => 'date',
            'documents_verified_at' => 'datetime',
            'gallery_paths'         => 'array',
            'career_timeline'       => 'array',
            'awards'                => 'array',
            'ai_generated_at'       => 'datetime',
            'editor_reviewed_at'    => 'datetime',
            'sealed_at'             => 'datetime',
            'included_until'        => 'date',
            'active_until'          => 'date',
            'use_sepia_treatment'   => 'boolean',
        ];
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    public function getAgeAttribute(): ?int
    {
        if (! $this->date_of_birth || ! $this->date_of_death) {
            return null;
        }

        return $this->date_of_birth->diffInYears($this->date_of_death);
    }

    public function isSealed(): bool
    {
        return $this->draft_status === 'sealed';
    }

    public function hasVerifiedDocuments(): bool
    {
        return $this->documents_verified_at !== null;
    }

    public function getPublicUrlAttribute(): string
    {
        return route('in-memoriam.profile', $this->slug);
    }

    public function getCommissioningCreditAttribute(): string
    {
        return $this->commissioned_by_display_value
            ?? $this->commissioned_by_org
            ?? $this->commissioned_by_name;
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'payable_id')
            ->where('payable_type', self::class);
    }

    public function editorialServiceRequests(): HasMany
    {
        return $this->hasMany(EditorialServiceRequest::class, 'serviceable_id')
            ->where('serviceable_type', self::class);
    }
}
