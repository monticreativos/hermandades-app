<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsurePortalRgpdAceptado
{
    public function handle(Request $request, Closure $next): Response
    {
        $cuenta = Auth::guard('portal')->user();

        if (! $cuenta) {
            return $next($request);
        }

        $hermano = $cuenta->hermano;
        if (! $hermano) {
            return $next($request);
        }

        if (! $hermano->rgpd_aceptado) {
            if ($request->isMethod('GET') && ! $request->ajax()) {
                $request->session()->put('url.intended', $request->fullUrl());
            }

            return redirect()->route('portal.rgpd.show');
        }

        return $next($request);
    }
}
