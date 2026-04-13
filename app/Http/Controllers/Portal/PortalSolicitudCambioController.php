<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Http\Requests\Portal\StoreSolicitudCambioDatosRequest;
use App\Models\SolicitudCambioDatos;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PortalSolicitudCambioController extends Controller
{
    public function create(): View|RedirectResponse
    {
        $cuenta = Auth::guard('portal')->user();
        $hermano = $cuenta->hermano;

        if ($hermano->solicitudesCambioDatos()->where('estado', SolicitudCambioDatos::ESTADO_PENDIENTE)->exists()) {
            return redirect()
                ->route('portal.perfil.index')
                ->with('error', 'Ya tiene una solicitud de cambio pendiente de revisión por secretaría.');
        }

        return view('portal.perfil.solicitud', [
            'cuenta' => $cuenta,
            'hermano' => $hermano,
        ]);
    }

    public function store(StoreSolicitudCambioDatosRequest $request): RedirectResponse
    {
        $cuenta = Auth::guard('portal')->user();
        $hermano = $cuenta->hermano;

        if ($hermano->solicitudesCambioDatos()->where('estado', SolicitudCambioDatos::ESTADO_PENDIENTE)->exists()) {
            return redirect()
                ->route('portal.perfil.index')
                ->with('error', 'Ya existe una solicitud pendiente.');
        }

        $cambios = $request->cambiosRespectoHermano($hermano);
        if ($cambios === []) {
            return back()->withInput()->with('error', 'No se detectaron cambios respecto a sus datos actuales.');
        }

        SolicitudCambioDatos::query()->create([
            'hermano_id' => $hermano->id,
            'hermano_portal_cuenta_id' => $cuenta->id,
            'ip_solicitud' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 512),
            'datos_solicitados' => $cambios,
            'estado' => SolicitudCambioDatos::ESTADO_PENDIENTE,
        ]);

        return redirect()
            ->route('portal.perfil.index')
            ->with('status', 'Solicitud enviada. Secretaría revisará los cambios y le notificará.');
    }
}
