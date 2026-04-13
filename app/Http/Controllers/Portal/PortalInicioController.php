<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\AvisoHermano;
use App\Models\ConfiguracionSalida;
use App\Models\Ejercicio;
use App\Models\PapeletaSitio;
use App\Models\SolicitudCambioDatos;
use Carbon\CarbonInterval;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class PortalInicioController extends Controller
{
    public function __invoke(): View
    {
        $cuenta = Auth::guard('portal')->user();
        $cuenta->load(['hermano.cuotaPendienteEjercicio', 'hermano.banco']);

        $hermano = $cuenta->hermano;

        $campañaActiva = ConfiguracionSalida::query()
            ->where('activa', true)
            ->orderByDesc('año')
            ->first();

        $repartoAbierto = $campañaActiva?->repartoAbierto() ?? false;

        $ejercicioCampaña = $campañaActiva
            ? Ejercicio::query()->where('año', $campañaActiva->año)->first()
            : null;

        $papeletaCampaña = $ejercicioCampaña
            ? PapeletaSitio::query()
                ->where('hermano_id', $hermano->id)
                ->where('ejercicio_id', $ejercicioCampaña->id)
                ->where('estado', '!=', PapeletaSitio::ESTADO_ANULADA)
                ->with(['ejercicio', 'insignia'])
                ->first()
            : null;

        $puedeAccionPapeleta = $hermano->estado === 'Alta'
            && ! $hermano->tieneCuotaOrdinariaPendiente()
            && ! $hermano->tieneDeuda();

        $mostrarBotonPapeleta = $repartoAbierto && $puedeAccionPapeleta;

        $solicitudCambioPendiente = $hermano->solicitudesCambioDatos()
            ->where('estado', SolicitudCambioDatos::ESTADO_PENDIENTE)
            ->exists();

        $tablonAvisos = AvisoHermano::query()
            ->where('hermano_id', $hermano->id)
            ->whereHas('aviso', fn ($q) => $q->where('visible_tablon', true)->whereNotNull('enviado_en'))
            ->with('aviso')
            ->orderByDesc('created_at')
            ->limit(12)
            ->get();

        $medallaAntiguedad = Cache::remember('portal.antiguedad.badge.'.$hermano->id, now()->addMinutes(10), function () use ($hermano): array {
            $antiguedad = $hermano->fecha_alta ? (int) $hermano->fecha_alta->diffInYears(now()) : 0;
            if ($antiguedad >= 75) {
                return ['titulo' => 'Medalla 75 años', 'color' => 'text-amber-700 bg-amber-100'];
            }
            if ($antiguedad >= 50) {
                return ['titulo' => 'Medalla 50 años', 'color' => 'text-amber-700 bg-amber-100'];
            }
            if ($antiguedad >= 25) {
                return ['titulo' => 'Medalla 25 años', 'color' => 'text-amber-700 bg-amber-100'];
            }

            return ['titulo' => 'Antigüedad inicial', 'color' => 'text-slate-700 bg-slate-100'];
        });

        $proximoEvento = Cache::remember('portal.proximo.evento.'.$hermano->id, now()->addMinutes(10), function () use ($campañaActiva) {
            if ($campañaActiva?->fecha_salida && $campañaActiva->fecha_salida->isFuture()) {
                $diff = CarbonInterval::seconds(now()->diffInSeconds($campañaActiva->fecha_salida, false))->cascade();

                return [
                    'titulo' => 'Estación de Penitencia '.$campañaActiva->año,
                    'fecha' => $campañaActiva->fecha_salida->format('d/m/Y'),
                    'cuentaAtras' => $diff->forHumans(['short' => true, 'parts' => 3]),
                ];
            }

            return null;
        });

        return view('portal.inicio', [
            'cuenta' => $cuenta,
            'hermano' => $hermano,
            'campañaActiva' => $campañaActiva,
            'repartoAbierto' => $repartoAbierto,
            'papeletaCampaña' => $papeletaCampaña,
            'puedeAccionPapeleta' => $puedeAccionPapeleta,
            'mostrarBotonPapeleta' => $mostrarBotonPapeleta,
            'solicitudCambioPendiente' => $solicitudCambioPendiente,
            'tablonAvisos' => $tablonAvisos,
            'medallaAntiguedad' => $medallaAntiguedad,
            'proximoEvento' => $proximoEvento,
        ]);
    }
}
