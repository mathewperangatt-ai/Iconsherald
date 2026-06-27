<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

/**
 * GoogleController — handles Google OAuth login via Laravel Socialite.
 *
 * There are two routes:
 *   GET /auth/google          → redirect the browser to Google's OAuth consent screen
 *   GET /auth/google/callback → Google redirects back here after the user authorises
 *
 * On first login, a new User record is created with no password.
 * On returning login, the existing record is found by google_id (preferred) or email.
 *
 * After a successful Google login the user is redirected to mobile OTP verification
 * if their mobile number is not yet verified. Only after both steps are complete
 * can they access member-area features (apply for a profile, view their application).
 */
class GoogleController extends Controller
{
    /**
     * Redirect to Google's OAuth consent screen.
     */
    public function redirect()
    {
        return Socialite::driver('google')
            ->scopes(['openid', 'profile', 'email'])
            ->redirect();
    }

    /**
     * Handle the callback from Google after the user authorises the app.
     */
    public function callback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Throwable $e) {
            return redirect()->route('home')
                ->withErrors(['auth' => 'Google login failed. Please try again.']);
        }

        // Find an existing user by google_id first, then fall back to email.
        $user = User::where('google_id', $googleUser->getId())->first()
            ?? User::where('email', $googleUser->getEmail())->first();

        if ($user) {
            // Keep the google_id and avatar up to date on every login.
            $user->update([
                'google_id'     => $googleUser->getId(),
                'google_avatar' => $googleUser->getAvatar(),
            ]);
        } else {
            // First-time login — create the user account.
            $user = User::create([
                'name'              => $googleUser->getName(),
                'email'             => $googleUser->getEmail(),
                'google_id'         => $googleUser->getId(),
                'google_avatar'     => $googleUser->getAvatar(),
                'email_verified_at' => now(),  // Google already verified the email.
                'role'              => 'member',
            ]);
        }

        Auth::login($user, remember: true);

        // If mobile OTP has not been verified yet, send them to verify first.
        if (! $user->hasMobileVerified()) {
            return redirect()->route('auth.otp.index');
        }

        return redirect()->intended(route('member.dashboard'));
    }
}
