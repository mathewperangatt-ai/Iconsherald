<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Profile model — the published editorial profile for a living professional.
 *
 * Lives at /register/{slug} on the public site.
 * Architecturally separate from LegacyProfile (In Memoriam).
 *
 * The draft_status field enforces "AI assists; humans publish":
 * a profile must pass through 'member_approved' before it can be 'published'.
 */
class Profile extends Model
{
    protected $fillable = [
        'user_id',
        'application_id',
        'slug',
        'package',
        'full_name',
        'credentials',
        'designation',
        'organisation',
        'location',
        'profession',
        'specialities',
        'chips',
        'biography',
        'excerpt',
        'portrait_path',
        'gallery_paths',
        'career_timeline',
        'awards',
        'draft_status',
        'ai_generated_at',
        'editor_reviewed_at',
        'sent_for_member_review_at',
        'member_approved_at',
        'published_at',
        'active_until',
        'is_founding_member',
        'qr_code_path',
    ];

    protected function casts(): array
    {
        return [
            'specialities'              => 'array',
            'chips'                     => 'array',
            'gallery_paths'             => 'array',
            'career_timeline'           => 'array',
            'awards'                    => 'array',
            'ai_generated_at'           => 'datetime',
            'editor_reviewed_at'        => 'datetime',
            'sent_for_member_review_at' => 'datetime',
            'member_approved_at'        => 'datetime',
            'published_at'              => 'datetime',
            'active_until'              => 'date',
            'is_founding_member'        => 'boolean',
        ];
    }

    // ── Draft status constants ────────────────────────────────────────────────

    const DRAFT_STATUS_DRAFT           = 'draft';
    const DRAFT_STATUS_AI_GENERATED    = 'ai_generated';
    const DRAFT_STATUS_EDITOR_REVIEWED = 'editor_reviewed';
    const DRAFT_STATUS_MEMBER_REVIEW   = 'member_review';
    const DRAFT_STATUS_MEMBER_APPROVED = 'member_approved';
    const DRAFT_STATUS_PUBLISHED       = 'published';
    const DRAFT_STATUS_UNPUBLISHED     = 'unpublished';

    // ── Relationships ─────────────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'payable_id')->where('payable_type', self::class);
    }

    public function editorialServiceRequests(): HasMany
    {
        return $this->hasMany(EditorialServiceRequest::class, 'serviceable_id')
            ->where('serviceable_type', self::class);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    public function isPublished(): bool
    {
        return $this->draft_status === self::DRAFT_STATUS_PUBLISHED;
    }

    public function isActive(): bool
    {
        return $this->isPublished() && ($this->active_until === null || $this->active_until->isFuture());
    }

    public function getPublicUrlAttribute(): string
    {
        return route('register.profile', $this->slug);
    }
}
