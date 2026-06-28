<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Profiles table — the published (or in-progress) editorial profile for a
 * living professional on the IconsHerald Register.
 *
 * This is structurally separate from legacy_profiles (In Memoriam) — the two
 * must never share a table. Cross-contamination between living and memorial
 * content is explicitly prohibited by the IconsHerald Constitution.
 *
 * Slug uniqueness is enforced at the application level against BOTH this table
 * and legacy_profiles (see SlugValidator service). Slugs are immutable once
 * published — any change request goes through admin.
 *
 * draft_status tracks the editorial pipeline:
 *   draft           → profile record created, no AI draft yet
 *   ai_generated    → Claude API has produced a first draft
 *   editor_reviewed → founder has reviewed and edited the draft
 *   member_review   → draft sent to the member for their approval
 *   member_approved → member has explicitly approved (timestamped)
 *   published       → live on the public Register
 *   unpublished     → temporarily hidden (admin action)
 *
 * The Constitution's principle "AI assists; humans publish" is enforced here:
 * a profile cannot move to 'published' without passing through 'member_approved'.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('application_id')->nullable()->constrained()->nullOnDelete();

            // Permanent public URL slug — globally unique, immutable after publish.
            $table->string('slug', 100)->unique();

            // Package tier (determines layout depth and sidebar fields).
            $table->enum('package', ['emerging', 'accomplished', 'distinguished']);

            // Core identity fields shown in the profile masthead.
            $table->string('full_name');
            $table->string('credentials')->nullable();     // e.g. "MBBS, MD"
            $table->string('designation')->nullable();
            $table->string('organisation')->nullable();
            $table->string('location')->nullable();

            // Broad profession category (Doctor, Lawyer, Architect, etc.)
            $table->string('profession');

            // Free-text specialities — searched by the Register's search box.
            // Stored as JSON array; kept separate from chips[] to avoid polluting search.
            $table->json('specialities')->nullable();

            // Small presentation badges shown on the profile card and page
            // (e.g. "Supreme Court Advocate", "15+ Years", "Published Author").
            $table->json('chips')->nullable();

            // Editorial biography — the primary content of the profile page.
            // Stored as HTML (admin edits in a rich-text editor; sanitised on render).
            $table->longText('biography')->nullable();

            // Short 2–3 sentence excerpt used on Register browse cards.
            $table->text('excerpt')->nullable();

            // Primary portrait photograph path.
            $table->string('portrait_path')->nullable();

            // Additional gallery images (Accomplished and Distinguished tiers).
            $table->json('gallery_paths')->nullable();

            // Career timeline items — JSON array of {year, title, description}.
            $table->json('career_timeline')->nullable();

            // Awards and recognition — JSON array of {year, title, body}.
            $table->json('awards')->nullable();

            // Draft pipeline status — gates publication.
            $table->string('draft_status')->default('draft');

            // Timestamps for key editorial events.
            $table->timestamp('ai_generated_at')->nullable();
            $table->timestamp('editor_reviewed_at')->nullable();
            $table->timestamp('sent_for_member_review_at')->nullable();
            $table->timestamp('member_approved_at')->nullable();
            $table->timestamp('published_at')->nullable();

            // Annual maintenance — profile stays 'active' while this date is in the future.
            $table->date('active_until')->nullable();

            // Internal founding-member flag (never rendered publicly).
            $table->boolean('is_founding_member')->default(false);

            // QR code path (generated on publish).
            $table->string('qr_code_path')->nullable();

            $table->timestamps();

            // Allow filtering profiles by status efficiently.
            $table->index('draft_status');
            $table->index('profession');
            $table->index('active_until');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profiles');
    }
};
