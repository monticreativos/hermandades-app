<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PortalEmailVerificationController extends Controller
{
    public function notice(Request $request): RedirectResponse|View
    {
        $cuenta = Auth::guard('portal')->user();
        if (! $cuenta) {
            return redirect()->route('portal.login');
        }

        if ($cuenta->hasVerifiedEmail()) {
            return redirect()->route('portal.inicio');
        }

        return view('portal.auth.verify-email');
    }

    public function send(Request $request): RedirectResponse
    {
        $cuenta = Auth::guard('portal')->user();
        if (! $cuenta) {
            return redirect()->route('portal.login');
        }

        if ($cuenta->hasVerifiedEmail()) {
            return redirect()->route('portal.inicio');
        }

        $cuenta->sendEmailVerificationNotification();

        return back()->with('status', 'verification-link-sent');
    }
}
