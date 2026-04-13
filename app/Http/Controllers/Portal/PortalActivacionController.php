<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Http\Requests\Portal\EstablecerContrasenaActivacionRequest;
use App\Http\Requests\Portal\EstablecerContrasenaPorCodigoRequest;
use App\Models\HermanoPortalCuenta;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PortalActivacionController extends Controller
{
    public function show(string $token): View|RedirectResponse
    {
        if (strlen($token) !== 64) {
            abort(404);
        }

        $cuenta = $this->buscarPorTokenActivacion($token);
        if (! $cuenta) {
            return redirect()
                ->route('portal.login')
                ->with('error', 'El enlace de activación no es válido o ha caducado. Solicite uno nuevo a la secretaría.');
        }

        if (filled($cuenta->password)) {
            return redirect()
                ->route('portal.login')
                ->with('status', 'Su acceso ya estaba activado. Inicie sesión con su correo y contraseña.');
        }

        return view('portal.activacion.establecer-contrasena', [
            'token' => $token,
            'email' => $cuenta->email,
        ]);
    }

    public function store(EstablecerContrasenaActivacionRequest $request): RedirectResponse
    {
        $cuenta = $this->buscarPorTokenActivacion($request->string('token')->toString());
        if (! $cuenta || filled($cuenta->password)) {
            return redirect()
                ->route('portal.login')
                ->with('error', 'No se pudo completar la activación. Solicite un nuevo enlace a la secretaría.');
        }

        $cuenta->forceFill([
            'password' => $request->validated('password'),
            'activacion_token_hash' => null,
            'activacion_expira' => null,
            'activacion_codigo_hash' => null,
            'activacion_codigo_expira' => null,
        ])->save();

        Auth::guard('portal')->login($cuenta);
        $request->session()->regenerate();

        $cuenta->sendEmailVerificationNotification();

        return redirect()
            ->route('portal.verification.notice')
            ->with('status', 'Contraseña creada. Revise su correo para verificar la dirección de email.');
    }

    public function createPorCodigo(): View
    {
        return view('portal.activacion.por-codigo', [
            'email' => old('email'),
        ]);
    }

    public function storePorCodigo(EstablecerContrasenaPorCodigoRequest $request): RedirectResponse
    {
        $cuenta = $this->buscarPorEmailYCodigoActivacion(
            $request->validated('email'),
            $request->validated('codigo')
        );

        if (! $cuenta || filled($cuenta->password)) {
            return redirect()
                ->route('portal.activacion.codigo')
                ->withInput($request->only('email'))
                ->with('error', 'Correo o código incorrectos, o el plazo de activación ha caducado. Solicite una nueva invitación a secretaría.');
        }

        $cuenta->forceFill([
            'password' => $request->validated('password'),
            'activacion_token_hash' => null,
            'activacion_expira' => null,
            'activacion_codigo_hash' => null,
            'activacion_codigo_expira' => null,
        ])->save();

        Auth::guard('portal')->login($cuenta);
        $request->session()->regenerate();

        $cuenta->sendEmailVerificationNotification();

        return redirect()
            ->route('portal.verification.notice')
            ->with('status', 'Contraseña creada. Revise su correo para verificar la dirección de email.');
    }

    private function buscarPorTokenActivacion(string $token): ?HermanoPortalCuenta
    {
        if (strlen($token) !== 64) {
            return null;
        }

        $hash = hash('sha256', $token);

        return HermanoPortalCuenta::query()
            ->where('activacion_token_hash', $hash)
            ->where('activacion_expira', '>', now())
            ->first();
    }

    private function buscarPorEmailYCodigoActivacion(string $email, string $codigo): ?HermanoPortalCuenta
    {
        $emailNorm = mb_strtolower(trim($email));

        $cuenta = HermanoPortalCuenta::query()
            ->whereRaw('LOWER(email) = ?', [$emailNorm])
            ->first();

        if (! $cuenta || ! $cuenta->activacion_codigo_hash || ! $cuenta->activacion_codigo_expira) {
            return null;
        }

        if ($cuenta->activacion_codigo_expira->isPast()) {
            return null;
        }

        $hash = hash('sha256', $codigo);

        return hash_equals($cuenta->activacion_codigo_hash, $hash) ? $cuenta : null;
    }
}
