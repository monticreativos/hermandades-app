<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\CuadrillaAviso;
use App\Models\EnsayoAsistencia;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PortalCuadrillaController extends Controller
{
    public function __invoke(): View
    {
        $cuenta = Auth::guard('portal')->user();
        $hermano = $cuenta->hermano;
        $perfil = $hermano->costaleroPerfil?->load(['cuadrilla.relevos.detalles']);

        $ensayos = collect();
        $faltas = 0;
        $avisos = collect();
        $relevoActual = null;
        if ($perfil?->cuadrilla_id) {
            $ensayos = EnsayoAsistencia::query()
                ->with('ensayo')
                ->where('hermano_id', $hermano->id)
                ->whereIn('ensayo_id', $perfil->cuadrilla->ensayos()->pluck('id'))
                ->orderByDesc('id')
                ->limit(20)
                ->get();
            $faltas = (int) $ensayos->where('asistio', false)->count();
            $avisos = CuadrillaAviso::query()
                ->where('cuadrilla_id', $perfil->cuadrilla_id)
                ->whereNotNull('enviado_en')
                ->latest('enviado_en')
                ->limit(10)
                ->get();
            $relevoActual = $perfil->cuadrilla->relevos()->latest('fecha_salida')->first();
        }

        return view('portal.cuadrilla.index', compact('hermano', 'perfil', 'ensayos', 'faltas', 'avisos', 'relevoActual'));
    }
}
