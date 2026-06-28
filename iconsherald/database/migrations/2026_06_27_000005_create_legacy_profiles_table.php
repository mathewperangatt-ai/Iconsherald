<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Legacy profiles table — the In Memoriam memorial archive.
 *
 * NEVER mixed with the profiles table. The Constitution mandates architectural
 * separation between living professionals and the memorial archive.
 *
 * Key differences from profiles:
 *  - Initiated by admin only (never self-service).
 *  - Requires three mandatory verification documents BEFORE any editorial work
 *    begins: death certificate, commissioning party ID, and a stated relationship.
 *  - Sealed at publication — no edits after publish without a Major Editorial
 *    Revision request (₹5,000 + GST).
 *  - Biography is always past-tense editorial.
 *  - Commissioning party credit replaces the "Request Contact" sidebar action.
 *  - 3 years included in the commission fee; optional continuation after that.
 *  - URL lives under /in-memoriam/{slug}, not /register/{slug}.
 *
 * draft_status mirrors the living Register's pipeline but uses 'sealed' instead
 * of 'published' to reinforce the permanence principle.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('legacy_profiles', function (Blueprint $table) {
            $table->id();

            // Not linked to a user — commissioning party is tracked separately below.
            // Admin creates and manages every In Memoriam commission directly.

            // Slug under /in-memoriam/{slug} — globally unique across BOTH tables.
            $table->string('slug', 100)->unique();

            // Subject's name and identity.
            $table->string('full_name');
            $table->string('credentials')->nullable();
            $table->string('designation')->nullable();
            $table->string('organisation')->nullable();
            $table->string('location')->nullable();
            $table->string('profession');

            // Dates of life — displayed prominently in the masthead.
            $table->date('date_of_birth')->nullable();
            $table->date('date_of_death')->nullable();

            // Commissioning party details (internal record — full name + org always kept).
            $table->string('commissioned_by_name');        // Always stored internally.
            $table->string('commissioned_by_org')->nullable();

            // What the commissioning party chooses to show publicly on the profile.
            // 'personal_name' | 'family_name' | 'institution_name'
            $table->string('commissioned_by_display_preference')->default('family_name');
            $table->string('commissioned_by_display_value')->nullable();  // Rendered string.

            // Mandatory verification documents (paths — reviewed by admin before proceeding).
            $table->string('death_certificate_path')->nullable();
            $table->string('commissioner_id_path')->nullable();
            $table->text('commissioner_relationship')->nullable();
            $table->timestamp('documents_verified_at')->nullable();   // Set by admin on review.

            // Editorial biography — always past tense.
            $table->longText('biography')->nullable();
            $table->text('excerpt')->nullable();
            $table->string('portrait_path')->nullable();
            $table->json('gallery_paths')->nullable();
            $table->json('career_timeline')->nullable();
            $table->json('awards')->nullable();

            // Sepia/warm-bronze visual treatment marker (always true for In Memoriam).
            // Kept as a field so the blade template can vary styling if needed.
            $table->boolean('use_sepia_treatment')->default(true);

            // Draft pipeline.
            $table->string('draft_status')->default('draft');
            $table->timestamp('ai_generated_at')->nullable();
            $table->timestamp('editor_reviewed_at')->nullable();
            $table->timestamp('sealed_at')->nullable();          // Equivalent of published_at.

            // 3 years included from commission — tracks when optional continuation begins.
            $table->date('included_until')->nullable();
            $table->date('active_until')->nullable();            // Extended by renewal payments.

            // Admin notes — internal only, never shown publicly.
            $table->text('admin_notes')->nullable();

            $table->timestamps();

            $table->index('draft_status');
            $table->index('profession');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('legacy_profiles');
    }
};
