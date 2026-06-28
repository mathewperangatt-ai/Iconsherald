<?php

use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\Auth\OtpController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\PublicController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// ── Public routes ─────────────────────────────────────────────────────────────

Route::get('/', [PublicController::class, 'home'])->name('home');
Route::get('/about', [PublicController::class, 'about'])->name('about');
Route::get('/pricing', [PublicController::class, 'pricing'])->name('pricing');
Route::get('/contact', [PublicController::class, 'contact'])->name('contact');
Route::get('/apply', [PublicController::class, 'apply'])->name('apply');

// Legal pages
Route::get('/terms', [PublicController::class, 'terms'])->name('terms');
Route::get('/privacy', [PublicController::class, 'privacy'])->name('privacy');
Route::get('/refund-policy', [PublicController::class, 'refundPolicy'])->name('refund-policy');
Route::get('/disclaimer', [PublicController::class, 'disclaimer'])->name('disclaimer');
Route::get('/grievance', [PublicController::class, 'grievance'])->name('grievance');

// The Register (living professionals)
Route::get('/register', [PublicController::class, 'registerIndex'])->name('register.index');
Route::get('/register/{slug}', [PublicController::class, 'registerProfile'])->name('register.profile');

// In Memoriam — /in-memoriam/{slug} (never /legacies — renamed per V9 Revision Brief)
Route::get('/in-memoriam', [PublicController::class, 'inMemoriamIndex'])->name('in-memoriam.index');
Route::get('/in-memoriam/{slug}', [PublicController::class, 'inMemoriamProfile'])->name('in-memoriam.profile');

// ── Google OAuth ──────────────────────────────────────────────────────────────

Route::get('/auth/google', [GoogleController::class, 'redirect'])->name('auth.google');
Route::get('/auth/google/callback', [GoogleController::class, 'callback'])->name('auth.google.callback');

// ── Mobile OTP verification (must be logged in) ───────────────────────────────

Route::middleware('auth')->group(function () {
    Route::get('/auth/otp', [OtpController::class, 'index'])->name('auth.otp.index');
    Route::post('/auth/otp/send', [OtpController::class, 'send'])->name('auth.otp.send');
    Route::post('/auth/otp/verify', [OtpController::class, 'verify'])->name('auth.otp.verify');
    Route::post('/auth/otp/resend', [OtpController::class, 'resend'])->name('auth.otp.resend');
});

// ── Logout ────────────────────────────────────────────────────────────────────

Route::post('/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect()->route('home');
})->middleware('auth')->name('logout');

// ── Member area (logged in + mobile verified) ─────────────────────────────────

Route::middleware(['auth', 'mobile.verified'])->group(function () {
    Route::get('/member/dashboard', [MemberController::class, 'dashboard'])->name('member.dashboard');
});
