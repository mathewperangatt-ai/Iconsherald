<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

/**
 * MemberController — the member-facing area (Phase 7 will expand this significantly).
 *
 * Phase 1 provides just the dashboard view so the post-OTP redirect works correctly.
 * Phase 7 will add profile status, profile preview, QR code download, renewal
 * management, editorial update requests, and payment history.
 */
class MemberController extends Controller
{
    public function dashboard(): View
    {
        return view('member.dashboard');
    }
}
