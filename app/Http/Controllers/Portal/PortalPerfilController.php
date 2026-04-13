<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\SolicitudCambioDatos;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PortalPerfilController extends Controller
{
    public function show(): View
    {
        $cuenta = Auth::guard('portal')->user();
        $cuenta->load('hermano.banco');
        $hermano = $cuenta->hermano;

        $solicitudPendiente = $hermano->solicitudesCambioDatos()
            ->where('estado', SolicitudCambioDatos::ESTADO_PENDIENTE)
            ->latest()
            ->first();

        return view('portal.perfil.show', [
            'cuenta' => $cuenta,
            'hermano' => $hermano,
            'solicitudPendiente' => $solicitudPendiente,
        ]);
    }
}
