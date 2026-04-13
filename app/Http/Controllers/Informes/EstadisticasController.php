<?php

namespace App\Http\Controllers\Informes;

use App\Http\Controllers\Controller;
use App\Models\ConfiguracionHermandad;
use App\Services\Informes\EstadisticasHermandadService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EstadisticasController extends Controller
{
    public function __construct(
        private readonly EstadisticasHermandadService $estadisticasHermandadService
    ) {}

    public function index(Request $request): View
    {
        $config = ConfiguracionHermandad::query()->first();
        $defAnt = max(0, min(80, (int) ($config?->censo_antiguedad_anos ?? 1)));

        $ref = $request->filled('fecha_ref')
            ? Carbon::parse($request->string('fecha_ref')->toString())->startOfDay()
            : Carbon::now()->startOfDay();

        $antiguedadVoto = $request->filled('antiguedad_voto')
            ? max(0, min(80, $request->integer('antiguedad_voto')))
            : $defAnt;

        $resumen = $this->estadisticasHermandadService->resumen($ref, $antiguedadVoto);

        return view('informes.estadisticas.index', [
            'resumen' => $resumen,
            'fechaRef' => $ref,
            'antiguedadVoto' => $antiguedadVoto,
            'defAntiguedadHermandad' => $defAnt,
        ]);
    }
}
