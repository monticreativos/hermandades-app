<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Http\Requests\Portal\AceptarRgpdPortalRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PortalRgpdController extends Controller
{
    public function show(): View|RedirectResponse
    {
        $cuenta = Auth::guard('portal')->user();
        if (! $cuenta || ! $cuenta->hasVerifiedEmail()) {
            return redirect()->route('portal.verification.notice');
        }

        $hermano = $cuenta->hermano;
        if ($hermano->rgpd_aceptado) {
            return redirect()->route('portal.inicio');
        }

        return view('portal.rgpd.aceptar', [
            'cuenta' => $cuenta,
            'hermano' => $hermano,
        ]);
    }

    public function accept(AceptarRgpdPortalRequest $request): RedirectResponse
    {
        $cuenta = Auth::guard('portal')->user();
        $hermano = $cuenta->hermano;

        if ($hermano->rgpd_aceptado) {
            return redirect()->route('portal.inicio');
        }

        $hermano->forceFill([
            'rgpd_aceptado' => true,
            'rgpd_fecha' => now(),
            'rgpd_ip' => $request->ip(),
        ])->save();

        return redirect()
            ->intended(route('portal.inicio'))
            ->with('status', 'Ha aceptado la información sobre protección de datos. Bienvenido al portal.');
    }
}
