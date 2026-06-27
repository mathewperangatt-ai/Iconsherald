<?php

namespace App\Http\Controllers;

use App\Models\LegacyProfile;
use App\Models\Profile;
use Illuminate\View\View;

/**
 * PublicController — serves all public-facing pages.
 *
 * These pages are server-rendered and do not require authentication.
 * Phase 2 will flesh each method out with real database queries and full Blade views.
 * For Phase 1, each method returns a stub view so routes are registered and testable.
 */
class PublicController extends Controller
{
    public function home(): View
    {
        return view('home');
    }

    public function about(): View
    {
        return view('public.about');
    }

    public function pricing(): View
    {
        return view('public.pricing');
    }

    public function contact(): View
    {
        return view('public.contact');
    }

    public function apply(): View
    {
        return view('public.apply');
    }

    // ── Legal pages ───────────────────────────────────────────────────────────

    public function terms(): View
    {
        return view('public.legal.terms');
    }

    public function privacy(): View
    {
        return view('public.legal.privacy');
    }

    public function refundPolicy(): View
    {
        return view('public.legal.refund-policy');
    }

    public function disclaimer(): View
    {
        return view('public.legal.disclaimer');
    }

    public function grievance(): View
    {
        return view('public.legal.grievance');
    }

    // ── The Register ──────────────────────────────────────────────────────────

    public function registerIndex(): View
    {
        // Phase 2 will add search, filters, and paginated profile cards.
        $profiles = Profile::where('draft_status', Profile::DRAFT_STATUS_PUBLISHED)
            ->latest('published_at')
            ->paginate(12);

        return view('public.register.index', compact('profiles'));
    }

    public function registerProfile(string $slug): View
    {
        $profile = Profile::where('slug', $slug)
            ->where('draft_status', Profile::DRAFT_STATUS_PUBLISHED)
            ->firstOrFail();

        return view('public.register.profile', compact('profile'));
    }

    // ── In Memoriam ───────────────────────────────────────────────────────────

    public function inMemoriamIndex(): View
    {
        $legacyProfiles = LegacyProfile::where('draft_status', 'sealed')
            ->latest('sealed_at')
            ->paginate(12);

        return view('public.in-memoriam.index', compact('legacyProfiles'));
    }

    public function inMemoriamProfile(string $slug): View
    {
        $legacyProfile = LegacyProfile::where('slug', $slug)
            ->where('draft_status', 'sealed')
            ->firstOrFail();

        return view('public.in-memoriam.profile', compact('legacyProfile'));
    }
}
