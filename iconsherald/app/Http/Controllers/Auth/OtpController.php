<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\SmsOtpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * OtpController — handles mobile OTP verification after Google login.
 *
 * This is NOT a login method. It is a mandatory second step that a freshly
 * Google-authenticated user must complete before accessing any member-area features.
 * The user is already logged in at this point; OTP simply verifies their mobile number.
 *
 * Routes:
 *   GET  /auth/otp           → show the mobile entry + OTP form
 *   POST /auth/otp/send      → validate the mobile number and dispatch the OTP
 *   POST /auth/otp/verify    → verify the submitted code and mark the user's mobile
 *   POST /auth/otp/resend    → resend a fresh OTP to the same number
 */
class OtpController extends Controller
{
    public function __construct(private SmsOtpService $otpService) {}

    /**
     * Show the OTP verification page.
     * Redirects away if the user is not logged in or already verified.
     */
    public function index()
    {
        if (! Auth::check()) {
            return redirect()->route('home');
        }

        if (Auth::user()->hasMobileVerified()) {
            return redirect()->route('member.dashboard');
        }

        return view('auth.otp');
    }

    /**
     * Accept a mobile number, send the OTP, and store the number in the session
     * temporarily (it is only saved to the user record after successful verification).
     */
    public function send(Request $request)
    {
        $request->validate([
            'mobile' => ['required', 'string', 'regex:/^[6-9]\d{9}$/'],
        ], [
            'mobile.regex' => 'Please enter a valid 10-digit Indian mobile number.',
        ]);

        $mobile = $request->mobile;

        // Check the number isn't already in use by a different account.
        $existing = \App\Models\User::where('mobile', $mobile)
            ->where('id', '!=', Auth::id())
            ->exists();

        if ($existing) {
            return back()->withErrors([
                'mobile' => 'This mobile number is linked to a different account.',
            ]);
        }

        $sent = $this->otpService->sendOtp(Auth::user(), $mobile);

        if (! $sent) {
            return back()->withErrors([
                'mobile' => 'We could not send the OTP. Please try again.',
            ]);
        }

        // Store number in session so the verify step knows what was sent.
        $request->session()->put('otp_mobile', $mobile);

        return back()->with('otp_sent', true)->with('otp_mobile', $mobile);
    }

    /**
     * Verify the submitted OTP code against the stored hash.
     */
    public function verify(Request $request)
    {
        $request->validate([
            'code' => ['required', 'string', 'digits:6'],
        ]);

        $result = $this->otpService->verifyOtp(Auth::user(), $request->code);

        if ($result !== true) {
            return back()->withErrors(['code' => $result]);
        }

        $request->session()->forget('otp_mobile');

        return redirect()->route('member.dashboard')
            ->with('success', 'Your mobile number has been verified. Welcome to IconsHerald.');
    }

    /**
     * Resend a fresh OTP to the same mobile number stored in the session.
     */
    public function resend(Request $request)
    {
        $mobile = $request->session()->get('otp_mobile');

        if (! $mobile) {
            return redirect()->route('auth.otp.index');
        }

        $sent = $this->otpService->sendOtp(Auth::user(), $mobile);

        if (! $sent) {
            return back()->withErrors(['code' => 'Could not resend OTP. Please try again.']);
        }

        return back()->with('otp_sent', true)->with('otp_mobile', $mobile);
    }
}
