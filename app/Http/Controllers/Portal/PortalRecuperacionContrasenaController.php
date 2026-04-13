<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Http\Requests\Portal\RestablecerContrasenaCodigoRequest;
use App\Http\Requests\Portal\SolicitarRecuperacionPortalRequest;
use App\Models\HermanoPortalCuenta;
use App\Notifications\PortalCodigoRecuperacionNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\View\View;

class PortalRecuperacionContrasenaController extends Controller
{
    public function create(): View
    {
        return view('portal.auth.forgot-password');
    }

    public function store(SolicitarRecuperacionPortalRequest $request): RedirectResponse
    {
        $key = 'portal-recuperar|'.$request->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            return back()->with('error', 'Demasiados intentos. Espere unos minutos.');
        }
        RateLimiter::hit($key, 300);

        $cuenta = HermanoPortalCuenta::query()
            ->where('email', $request->validated('email'))
            ->first();

        if ($cuenta && filled($cuenta->password)) {
            $codigo = (string) random_int(100000, 999999);
            $cuenta->forceFill([
                'recuperacion_codigo_hash' => Hash::make($codigo),
                'recuperacion_expira' => now()->addMinutes(10),
            ])->save();

            $cuenta->notify(new PortalCodigoRecuperacionNotification($codigo));
        }

        $request->session()->put('portal_recovery_email', $request->validated('email'));

        return redirect()
            ->route('portal.recuperar.codigo')
            ->with('status', 'Si el correo está registrado en el portal, recibirá un código de 6 dígitos. Caduca en 10 minutos.');
    }

    public function showCodigo(): View|RedirectResponse
    {
        if (! session()->has('portal_recovery_email')) {
            return redirect()->route('portal.recuperar.request');
        }

        return view('portal.auth.recuperar-codigo', [
            'email' => session('portal_recovery_email'),
        ]);
    }

    public function restablecer(RestablecerContrasenaCodigoRequest $request): RedirectResponse
    {
        $email = session('portal_recovery_email');
        if (! $email) {
            return redirect()->route('portal.recuperar.request');
        }

        $cuenta = HermanoPortalCuenta::query()->where('email', $email)->first();
        if (! $cuenta || ! $cuenta->recuperacion_codigo_hash || ! $cuenta->recuperacion_expira) {
            return back()->with('error', 'No hay una solicitud de recuperación activa. Solicite un código nuevo.');
        }

        if (now()->greaterThan($cuenta->recuperacion_expira)) {
            return back()->with('error', 'El código ha caducado. Solicite uno nuevo.');
        }

        if (! Hash::check($request->validated('codigo'), $cuenta->recuperacion_codigo_hash)) {
            return back()->with('error', 'Código incorrecto.');
        }

        $cuenta->forceFill([
            'password' => $request->validated('password'),
            'recuperacion_codigo_hash' => null,
            'recuperacion_expira' => null,
        ])->save();

        $request->session()->forget('portal_recovery_email');

        return redirect()
            ->route('portal.login')
            ->with('status', 'Contraseña actualizada. Ya puede iniciar sesión.');
    }
}
