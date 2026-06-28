<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Applications table — the first step in the IconsHerald editorial process.
 *
 * An applicant submits their details and chosen package; the admin reviews
 * and either approves (moving to the questionnaire/payment stage) or declines.
 *
 * Statuses follow the documented workflow:
 *   pending      → just submitted, awaiting admin review
 *   approved     → admin approved; applicant may now complete the questionnaire
 *   declined     → admin declined with a written reason
 *   questionnaire_sent → questionnaire link sent to the applicant
 *   questionnaire_complete → applicant has submitted all answers
 *   payment_pending → upfront (50%) Razorpay link has been sent
 *   payment_received → upfront payment confirmed via webhook
 *   in_editorial  → editorial team is composing the biography
 *   member_review → draft sent to member for approval
 *   member_approved → member has explicitly approved the draft
 *   balance_pending → balance (50%) payment link sent
 *   published    → profile is live on the Register
 *   withdrawn    → applicant withdrew their application
 *
 * Package tiers: 'emerging' | 'accomplished' | 'distinguished'
 *
 * Emerging is deliberately lighter: applicant submits a résumé/CV +
 * one photograph only; no questionnaire is presented. The editorial team
 * composes the profile directly from the submitted document.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // Package tier chosen at application time.
            $table->enum('package', ['emerging', 'accomplished', 'distinguished']);

            // Desired vanity URL slug (e.g. "dr-preethi-menon").
            // Checked for uniqueness against both profiles and legacy_profiles tables.
            $table->string('desired_slug', 100)->nullable();

            // Current workflow status — drives which screens the admin sees.
            $table->string('status')->default('pending');

            // Admin decision fields.
            $table->text('admin_notes')->nullable();
            $table->text('decline_reason')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();

            // Emerging-tier document upload (résumé/CV path on disk).
            // Accomplished and Distinguished use the questionnaire instead.
            $table->string('resume_path')->nullable();

            // Applicant's submitted photograph path (one photo for Emerging).
            $table->string('photo_path')->nullable();

            // Basic applicant details collected at application stage.
            $table->string('full_name');
            $table->string('profession');           // Broad category (Doctor, Lawyer, etc.)
            $table->string('designation')->nullable();
            $table->string('organisation')->nullable();
            $table->string('location')->nullable();

            // Internal Filament founding-member flag (never rendered publicly).
            $table->boolean('is_founding_member')->default(false);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('applications');
    }
};
