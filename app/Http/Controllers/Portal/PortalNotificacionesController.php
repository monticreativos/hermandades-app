<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\AvisoHermano;
use App\Models\SolicitudCambioDatos;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PortalNotificacionesController extends Controller
{
    public function __invoke(): View
    {
        $cuenta = Auth::guard('portal')->user();
        $hermano = $cuenta->hermano;

        $ultimasSolicitudes = $hermano->solicitudesCambioDatos()
            ->whereIn('estado', [SolicitudCambioDatos::ESTADO_APROBADA, SolicitudCambioDatos::ESTADO_RECHAZADA])
            ->latest('updated_at')
            ->limit(15)
            ->get();

        $comunicados = AvisoHermano::query()
            ->where('hermano_id', $hermano->id)
            ->whereHas('aviso', fn ($q) => $q->whereNotNull('enviado_en'))
            ->with('aviso')
            ->orderByDesc('created_at')
            ->limit(40)
            ->get();

        return view('portal.notificaciones.index', [
            'cuenta' => $cuenta,
            'hermano' => $hermano,
            'ultimasSolicitudes' => $ultimasSolicitudes,
            'comunicados' => $comunicados,
        ]);
    }
}
