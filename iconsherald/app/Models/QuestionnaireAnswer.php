<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * QuestionnaireAnswer model — a single question/answer pair.
 *
 * Answers are upserted on every auto-save, keyed on (application_id, question_key).
 * This table is passed to the Claude API for biography drafting.
 */
class QuestionnaireAnswer extends Model
{
    protected $fillable = [
        'application_id',
        'question_key',
        'section',
        'answer',
    ];

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }
}
