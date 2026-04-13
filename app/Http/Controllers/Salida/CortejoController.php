<?php

namespace App\Http\Controllers\Salida;

use App\Http\Controllers\Controller;
use App\Models\Ejercicio;
use App\Models\Insignia;
use App\Models\PapeletaSitio;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CortejoController extends Controller
{
    public function index(Request $request): View
    {
        $ejercicioActual = Ejercicio::query()
            ->where('estado', Ejercicio::ESTADO_ABIERTO)
            ->orderByDesc('año')
            ->first();

        $ejercicioId = $request->filled('ejercicio_id')
            ? $request->integer('ejercicio_id')
            : $ejercicioActual?->id;

        $papeletas = PapeletaSitio::query()
            ->with(['hermano', 'insignia'])
            ->where('papeletas_sitio.ejercicio_id', $ejercicioId)
            ->where('papeletas_sitio.estado', '!=', 'Anulada')
            ->join('hermanos', 'papeletas_sitio.hermano_id', '=', 'hermanos.id')
            ->orderBy('papeletas_sitio.tramo')
            ->orderBy('hermanos.numero_hermano')
            ->select('papeletas_sitio.*')
            ->get();

        $tramosAgrupados = $papeletas->groupBy('tramo');

        $insignias = Insignia::query()->orderBy('orden')->get();
        $ejercicios = Ejercicio::query()->orderByDesc('año')->get();

        return view('salida.cortejo.index', [
            'tramosAgrupados' => $tramosAgrupados,
            'insignias' => $insignias,
            'ejercicios' => $ejercicios,
            'ejercicioId' => $ejercicioId,
            'ejercicioActual' => $ejercicioActual,
            'totalPapeletas' => $papeletas->count(),
        ]);
    }
}
