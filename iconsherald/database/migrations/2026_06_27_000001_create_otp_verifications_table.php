<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * OTP verifications table — short-lived records for mobile number verification.
 *
 * The OTP code itself is hashed before storage (never stored in plain text).
 * Each row is deleted as soon as it is used, or pruned by a scheduled job
 * once expires_at passes. We never keep codes longer than their validity window.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('otp_verifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('mobile', 15);

            // Hashed OTP — never store the raw code.
            $table->string('code_hash');

            // Short expiry window (10 minutes is standard for OTP flows).
            $table->timestamp('expires_at');

            // Track how many times an unverified user has tried this code.
            // Lock out after 5 attempts to prevent brute-force guessing.
            $table->unsignedTinyInteger('attempts')->default(0);

            $table->boolean('verified')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('otp_verifications');
    }
};
