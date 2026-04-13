<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsurePortalEmailVerified
{
    public function handle(Request $request, Closure $next): Response
    {
        $cuenta = Auth::guard('portal')->user();

        if (! $cuenta || ! $cuenta->hasVerifiedEmail()) {
            return redirect()->route('portal.verification.notice');
        }

        return $next($request);
    }
}
