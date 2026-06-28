<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Users table — every person who logs in to IconsHerald sits here.
 *
 * Authentication is Google OAuth only (no stored password).
 * Mobile OTP is a mandatory second step, tracked via mobile_verified_at.
 * Role drives access: 'member' sees their own application/profile;
 * 'admin' gets the Filament editorial panel; 'editor' (future) is kept ready.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();

            // Password is nullable — Google OAuth accounts have no password.
            $table->string('password')->nullable();

            // Google OAuth fields — populated on first login via Socialite.
            $table->string('google_id')->nullable()->unique();
            $table->string('google_avatar')->nullable();

            // Mobile OTP verification — number stored only after OTP confirmed.
            $table->string('mobile', 15)->nullable()->unique();
            $table->timestamp('mobile_verified_at')->nullable();

            // Role: 'member' | 'admin' | 'editor'
            $table->string('role')->default('member');

            // Internal flag: admin can mark a profile as a comped/founding invite.
            // This is never displayed publicly — admin bookkeeping only.
            $table->boolean('is_founding_member')->default(false);

            $table->rememberToken();
            $table->timestamps();
        });

        // Password reset tokens — kept even though OAuth is the only login method,
        // in case email/password is ever added for admin accounts.
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        // Database-backed sessions (more secure than file-based on shared hosting).
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
