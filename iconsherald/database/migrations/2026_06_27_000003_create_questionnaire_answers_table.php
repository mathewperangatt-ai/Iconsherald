<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Questionnaire answers table — stores every answer the applicant gives
 * during the structured interview questionnaire.
 *
 * Answers are stored as individual rows (one per question) rather than
 * a single JSON blob. This makes them easy to display in the admin review
 * screen, pass cleanly to the Claude API for drafting, and audit individually.
 *
 * The questionnaire is multi-section and auto-saves — each keypress that
 * leaves a field triggers an upsert against (application_id, question_key).
 * Accomplished and Distinguished applicants use this; Emerging applicants
 * submit a résumé instead (see applications table).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('questionnaire_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained()->cascadeOnDelete();

            // Machine-readable key matching the question definition
            // (e.g. "professional_identity.who_are_you", "career.proudest_achievement").
            $table->string('question_key', 100);

            // Section name for grouping in the admin review view.
            $table->string('section', 100);

            // The actual answer text from the applicant.
            $table->text('answer')->nullable();

            $table->timestamps();

            // One answer per question per application.
            $table->unique(['application_id', 'question_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('questionnaire_answers');
    }
};
