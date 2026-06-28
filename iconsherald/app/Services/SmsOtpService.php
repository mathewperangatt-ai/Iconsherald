<?php

namespace App\Services;

use App\Models\OtpVerification;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * SmsOtpService — generates, sends, and verifies mobile OTP codes.
 *
 * OTP generation:
 *   - A 6-digit code is generated using a cryptographically secure random source.
 *   - The code is hashed with bcrypt before storage — the raw code is never persisted.
 *   - Each OTP expires after 10 minutes.
 *   - After 5 failed verification attempts the record is locked (brute-force protection).
 *   - Any previous unverified OTP for the same user is deleted before creating a new one.
 *
 * SMS delivery:
 *   - Primary provider: MSG91 (India).
 *   - In local/testing environments the OTP is logged instead of sent (no real SMS).
 *   - Switching to Twilio later requires only adding a new delivery method below.
 *
 * Security note from the Master Build Reference:
 *   Store only the verified mobile number and a verified_at timestamp — never store
 *   OTP codes themselves beyond their short validation window.
 */
class SmsOtpService
{
    private const EXPIRY_MINUTES = 10;
    private const MAX_ATTEMPTS   = 5;

    /**
     * Generate and send a fresh OTP to the given mobile number for a user.
     *
     * Returns true if the SMS was dispatched (or logged in dev), false on failure.
     */
    public function sendOtp(User $user, string $mobile): bool
    {
        // Remove any existing unverified OTP for this user.
        OtpVerification::where('user_id', $user->id)->where('verified', false)->delete();

        $rawCode = $this->generateCode();

        OtpVerification::create([
            'user_id'    => $user->id,
            'mobile'     => $mobile,
            'code_hash'  => Hash::make($rawCode),
            'expires_at' => now()->addMinutes(self::EXPIRY_MINUTES),
            'attempts'   => 0,
            'verified'   => false,
        ]);

        return $this->deliverOtp($mobile, $rawCode);
    }

    /**
     * Verify the code submitted by the user.
     *
     * Returns true and marks the user's mobile as verified on success.
     * Returns a string error message on failure.
     */
    public function verifyOtp(User $user, string $rawCode): bool|string
    {
        $record = OtpVerification::where('user_id', $user->id)
            ->where('verified', false)
            ->latest()
            ->first();

        if (! $record) {
            return 'No active OTP found. Please request a new code.';
        }

        if ($record->isExpired()) {
            $record->delete();
            return 'The OTP has expired. Please request a new code.';
        }

        if ($record->isLocked()) {
            return 'Too many failed attempts. Please request a new OTP.';
        }

        if (! $record->checkCode($rawCode)) {
            $record->increment('attempts');
            $remaining = self::MAX_ATTEMPTS - $record->fresh()->attempts;
            return "Incorrect code. {$remaining} attempt(s) remaining.";
        }

        // Code is correct — mark the user's mobile as verified.
        $record->update(['verified' => true]);
        $record->delete();  // No longer needed — clean up immediately.

        $user->update([
            'mobile'             => $record->mobile,
            'mobile_verified_at' => now(),
        ]);

        return true;
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    private function generateCode(): string
    {
        return str_pad((string) random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
    }

    private function deliverOtp(string $mobile, string $code): bool
    {
        // In local or testing environments, log the OTP instead of sending a real SMS.
        if (app()->environment(['local', 'testing'])) {
            Log::info("IconsHerald OTP [{$mobile}]: {$code}");
            return true;
        }

        return $this->sendViaMSG91($mobile, $code);
    }

    /**
     * Send OTP via MSG91 (India's primary transactional SMS provider).
     *
     * Requires DLT template registration before going live — see the Master
     * Build Reference, Section 12, "SMS OTP provider" open question.
     */
    private function sendViaMSG91(string $mobile, string $code): bool
    {
        $authKey    = config('services.msg91.auth_key');
        $senderId   = config('services.msg91.sender_id');
        $templateId = config('services.msg91.template_id');

        if (! $authKey || ! $templateId) {
            Log::warning('MSG91 credentials not configured — OTP not sent.');
            return false;
        }

        try {
            $response = Http::withHeaders(['authkey' => $authKey])
                ->post('https://api.msg91.com/api/v5/otp', [
                    'template_id' => $templateId,
                    'mobile'      => '91' . ltrim($mobile, '+91'),
                    'authkey'     => $authKey,
                    'otp'         => $code,
                    'sender'      => $senderId,
                ]);

            if ($response->successful() && $response->json('type') === 'success') {
                return true;
            }

            Log::warning('MSG91 OTP delivery failed', ['response' => $response->json()]);
            return false;

        } catch (\Throwable $e) {
            Log::error('MSG91 exception: ' . $e->getMessage());
            return false;
        }
    }
}
