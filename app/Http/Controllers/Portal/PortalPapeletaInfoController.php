<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\ConfiguracionHermandad;
use App\Models\ConfiguracionSalida;
use App\Models\Ejercicio;
use App\Models\PapeletaSitio;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PortalPapeletaInfoController extends Controller
{
    public function __invoke(): View|RedirectResponse
    {
        $cuenta = Auth::guard('portal')->user();
        $hermano = $cuenta->hermano;

        $campaña = ConfiguracionSalida::query()
            ->where('activa', true)
            ->orderByDesc('año')
            ->first();

        $repartoAbierto = $campaña?->repartoAbierto() ?? false;

        if (! $repartoAbierto || ! $campaña) {
            return redirect()
                ->route('portal.inicio')
                ->with('error', 'En este momento no está abierto el periodo de reparto de papeletas de sitio.');
        }

        if ($hermano->estado !== 'Alta') {
            return redirect()
                ->route('portal.inicio')
                ->with('error', 'Solo los hermanos en alta pueden solicitar papeleta en el periodo de reparto.');
        }

        if ($hermano->tieneCuotaOrdinariaPendiente()) {
            return redirect()
                ->route('portal.pagos.index')
                ->with('error', 'Debe estar al corriente de la cuota ordinaria para el reparto de papeletas.');
        }

        if ($hermano->tieneDeuda()) {
            return redirect()
                ->route('portal.pagos.index')
                ->with('error', 'Regularice la deuda de lotería u otros conceptos antes de solicitar papeleta.');
        }

        $ejercicio = Ejercicio::query()->where('año', $campaña->año)->first();
        $papeletaExistente = $ejercicio
            ? PapeletaSitio::query()
                ->where('hermano_id', $hermano->id)
                ->where('ejercicio_id', $ejercicio->id)
                ->where('estado', '!=', PapeletaSitio::ESTADO_ANULADA)
                ->first()
            : null;

        return view('portal.papeleta.info', [
            'cuenta' => $cuenta,
            'hermano' => $hermano,
            'campaña' => $campaña,
            'papeletaExistente' => $papeletaExistente,
            'configuracionHermandad' => ConfiguracionHermandad::query()->first(),
        ]);
    }
}
