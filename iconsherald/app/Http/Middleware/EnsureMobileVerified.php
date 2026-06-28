<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * EnsureMobileVerified — gates any route that requires a verified mobile number.
 *
 * Called 'mobile.verified' in route definitions.
 * Users who haven't yet verified their mobile are redirected to the OTP page.
 * Admin accounts bypass this check — they authenticate directly via Filament.
 */
class EnsureMobileVerified
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (! $user) {
            return redirect()->route('auth.google');
        }

        // Admins manage their own session via Filament; OTP check doesn't apply.
        if ($user->isAdmin()) {
            return $next($request);
        }

        if (! $user->hasMobileVerified()) {
            return redirect()->route('auth.otp.index');
        }

        return $next($request);
    }
}
