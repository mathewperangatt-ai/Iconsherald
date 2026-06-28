<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * EditorialServiceRequest model — a post-publication update or revision request.
 *
 * Either a member (via their dashboard, Phase 7) or the admin can raise these.
 * Minor Update: ₹3,000 + GST.
 * Major Revision: ₹5,000 + GST — also covers package tier upgrades at the same rate.
 */
class EditorialServiceRequest extends Model
{
    protected $fillable = [
        'user_id',
        'serviceable_type',
        'serviceable_id',
        'service_type',
        'upgrade_to_package',
        'request_description',
        'status',
        'admin_notes',
        'payment_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function serviceable(): MorphTo
    {
        return $this->morphTo();
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }
}
