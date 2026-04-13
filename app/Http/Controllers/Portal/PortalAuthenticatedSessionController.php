<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Http\Requests\Portal\PortalLoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PortalAuthenticatedSessionController extends Controller
{
    public function create(): View
    {
        return view('portal.auth.login');
    }

    public function store(PortalLoginRequest $request): RedirectResponse
    {
        $request->authenticate();
        $request->session()->regenerate();

        $cuenta = Auth::guard('portal')->user();
        if ($cuenta && ! $cuenta->hasVerifiedEmail()) {
            return redirect()->route('portal.verification.notice');
        }

        return redirect()->intended(route('portal.inicio', absolute: false));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('portal')->logout();
        $request->session()->regenerateToken();

        return redirect()->route('portal.login');
    }
}
