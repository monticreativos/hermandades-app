<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\HermanoPortalCuenta;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PortalVerifyEmailController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        $cuenta = HermanoPortalCuenta::query()->findOrFail($request->route('id'));

        if (! hash_equals((string) $request->route('hash'), sha1($cuenta->getEmailForVerification()))) {
            abort(403);
        }

        if ($cuenta->hasVerifiedEmail()) {
            $this->loginSiCoincide($request, $cuenta);

            return redirect()->route('portal.inicio')->with('status', 'email-already-verified');
        }

        if ($cuenta->markEmailAsVerified()) {
            event(new Verified($cuenta));
        }

        $this->loginSiCoincide($request, $cuenta);

        return redirect()->route('portal.inicio')->with('status', 'email-verified');
    }

    private function loginSiCoincide(Request $request, HermanoPortalCuenta $cuenta): void
    {
        if (! Auth::guard('portal')->check() || Auth::guard('portal')->id() !== $cuenta->id) {
            Auth::guard('portal')->login($cuenta);
            $request->session()->regenerate();
        }
    }
}
