<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Application model — an applicant's submission for a place on the Register.
 *
 * Moves through a documented status pipeline from 'pending' through
 * admin review, questionnaire, payment, editorial work, and publication.
 *
 * Emerging-tier applications use a résumé/CV upload path (no questionnaire).
 * Accomplished and Distinguished use the multi-section questionnaire.
 */
class Application extends Model
{
    protected $fillable = [
        'user_id',
        'package',
        'desired_slug',
        'status',
        'admin_notes',
        'decline_reason',
        'reviewed_by',
        'reviewed_at',
        'resume_path',
        'photo_path',
        'full_name',
        'profession',
        'designation',
        'organisation',
        'location',
        'is_founding_member',
    ];

    protected function casts(): array
    {
        return [
            'reviewed_at'       => 'datetime',
            'is_founding_member' => 'boolean',
        ];
    }

    // ── Status constants ──────────────────────────────────────────────────────

    const STATUS_PENDING                 = 'pending';
    const STATUS_APPROVED                = 'approved';
    const STATUS_DECLINED                = 'declined';
    const STATUS_QUESTIONNAIRE_SENT      = 'questionnaire_sent';
    const STATUS_QUESTIONNAIRE_COMPLETE  = 'questionnaire_complete';
    const STATUS_PAYMENT_PENDING         = 'payment_pending';
    const STATUS_PAYMENT_RECEIVED        = 'payment_received';
    const STATUS_IN_EDITORIAL            = 'in_editorial';
    const STATUS_MEMBER_REVIEW           = 'member_review';
    const STATUS_MEMBER_APPROVED         = 'member_approved';
    const STATUS_BALANCE_PENDING         = 'balance_pending';
    const STATUS_PUBLISHED               = 'published';
    const STATUS_WITHDRAWN               = 'withdrawn';

    // ── Relationships ─────────────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function answers(): HasMany
    {
        return $this->hasMany(QuestionnaireAnswer::class);
    }

    public function profile(): HasOne
    {
        return $this->hasOne(Profile::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'payable_id')->where('payable_type', self::class);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    public function isEmerging(): bool
    {
        return $this->package === 'emerging';
    }

    public function usesQuestionnaire(): bool
    {
        return in_array($this->package, ['accomplished', 'distinguished']);
    }
}
