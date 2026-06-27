<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Editorial service requests — post-publication update or revision requests.
 *
 * A member (or admin on their behalf) can request a Minor Update (₹3,000 + GST)
 * or a Major Revision (₹5,000 + GST, also covers package tier upgrades).
 *
 * Statuses:
 *   requested   → member or admin has created the request
 *   payment_sent → admin has sent the Razorpay link
 *   paid        → payment confirmed via webhook
 *   in_progress → editorial team is working on it
 *   complete    → changes applied to the published profile
 *   declined    → admin declined the request (with a reason)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('editorial_service_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // Can be for a living profile or a legacy profile.
            $table->nullableMorphs('serviceable');  // serviceable_type + serviceable_id

            // 'minor_update' | 'major_revision' (major also covers tier upgrades)
            $table->string('service_type');

            // If a tier upgrade: the requested new package.
            $table->string('upgrade_to_package')->nullable();

            // Free-text description of what the member wants changed.
            $table->text('request_description');

            $table->string('status')->default('requested');
            $table->text('admin_notes')->nullable();

            // Linked payment record — created when admin generates the payment link.
            $table->foreignId('payment_id')->nullable()->constrained()->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('editorial_service_requests');
    }
};
