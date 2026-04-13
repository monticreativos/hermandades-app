<?php

namespace App\Http\Controllers\Salida;

use App\Http\Controllers\Controller;
use App\Models\ConfiguracionSalida;
use App\Models\Ejercicio;
use App\Models\Hermano;
use App\Models\Insignia;
use App\Models\PapeletaSitio;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PapeletaController extends Controller
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
            ->with(['hermano', 'ejercicio', 'insignia'])
            ->when($ejercicioId, fn ($q) => $q->where('ejercicio_id', $ejercicioId))
            ->when($request->filled('estado'), fn ($q) => $q->where('estado', $request->string('estado')))
            ->when($request->filled('puesto'), fn ($q) => $q->where('puesto', $request->string('puesto')))
            ->when($request->filled('tramo'), fn ($q) => $q->where('tramo', $request->string('tramo')))
            ->when($request->filled('q'), function ($q) use ($request): void {
                $search = $request->string('q')->toString();
                $q->whereHas('hermano', fn ($h) => $h->where('nombre', 'like', "%{$search}%")
                    ->orWhere('apellidos', 'like', "%{$search}%")
                    ->orWhere('numero_hermano', 'like', "%{$search}%"));
            })
            ->join('hermanos', 'papeletas_sitio.hermano_id', '=', 'hermanos.id')
            ->orderBy('hermanos.numero_hermano')
            ->select('papeletas_sitio.*')
            ->paginate(50)
            ->withQueryString();

        $config = ConfiguracionSalida::query()
            ->when($ejercicioActual, fn ($q) => $q->where('año', $ejercicioActual->año))
            ->first();

        $ejercicios = Ejercicio::query()->orderByDesc('año')->get();
        $insignias = Insignia::query()->orderBy('orden')->get();

        $stats = [
            'total' => PapeletaSitio::query()->when($ejercicioId, fn ($q) => $q->where('ejercicio_id', $ejercicioId))->count(),
            'emitidas' => PapeletaSitio::query()->when($ejercicioId, fn ($q) => $q->where('ejercicio_id', $ejercicioId))->where('estado', 'Emitida')->count(),
            'solicitadas' => PapeletaSitio::query()->when($ejercicioId, fn ($q) => $q->where('ejercicio_id', $ejercicioId))->where('estado', 'Solicitada')->count(),
            'anuladas' => PapeletaSitio::query()->when($ejercicioId, fn ($q) => $q->where('ejercicio_id', $ejercicioId))->where('estado', 'Anulada')->count(),
            'donativos' => PapeletaSitio::query()->when($ejercicioId, fn ($q) => $q->where('ejercicio_id', $ejercicioId))->where('estado', '!=', 'Anulada')->sum('donativo_pagado'),
        ];

        return view('salida.papeletas.index', [
            'papeletas' => $papeletas,
            'ejercicios' => $ejercicios,
            'ejercicioActual' => $ejercicioActual,
            'ejercicioId' => $ejercicioId,
            'insignias' => $insignias,
            'config' => $config,
            'stats' => $stats,
        ]);
    }

    public function buscarHermano(Request $request): JsonResponse
    {
        $q = trim((string) $request->string('q'));

        $hermanos = Hermano::query()
            ->where('estado', 'Alta')
            ->when($q !== '', fn ($query) => $query->where(function ($sub) use ($q): void {
                $sub->where('nombre', 'like', "%{$q}%")
                    ->orWhere('apellidos', 'like', "%{$q}%")
                    ->orWhere('numero_hermano', 'like', "%{$q}%")
                    ->orWhere('dni', 'like', "%{$q}%");
            }))
            ->orderBy('numero_hermano')
            ->limit(20)
            ->get();

        $ejercicioActual = Ejercicio::query()
            ->where('estado', Ejercicio::ESTADO_ABIERTO)
            ->orderByDesc('año')
            ->first();

        return response()->json([
            'hermanos' => $hermanos->map(fn (Hermano $h) => [
                'id' => $h->id,
                'numero_hermano' => $h->numero_hermano,
                'nombre' => $h->nombre,
                'apellidos' => $h->apellidos,
                'nombre_completo' => $h->nombreCompleto(),
                'fecha_alta' => $h->fecha_alta?->format('d/m/Y'),
                'estado' => $h->estado,
                'deuda_loteria' => $h->deudaLoteria(),
                'tiene_deuda' => $h->tieneDeuda(),
                'tiene_papeleta' => $ejercicioActual
                    ? $h->papeletas()->where('ejercicio_id', $ejercicioActual->id)->exists()
                    : false,
            ]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'hermano_id' => 'required|exists:hermanos,id',
            'ejercicio_id' => 'required|exists:ejercicios,id',
            'puesto' => 'required|string|max:100',
            'insignia_id' => 'nullable|exists:insignias,id',
            'tramo' => 'nullable|string|max:50',
            'donativo_pagado' => 'required|numeric|min:0',
            'notas' => 'nullable|string|max:1000',
        ]);

        $existe = PapeletaSitio::query()
            ->where('hermano_id', $data['hermano_id'])
            ->where('ejercicio_id', $data['ejercicio_id'])
            ->exists();

        if ($existe) {
            return back()->with('error', 'Este hermano ya tiene papeleta emitida para este ejercicio.');
        }

        $data['estado'] = PapeletaSitio::ESTADO_EMITIDA;

        PapeletaSitio::create($data);

        return back()->with('status', 'Papeleta de sitio emitida correctamente.');
    }

    public function update(Request $request, PapeletaSitio $papeleta): RedirectResponse
    {
        $data = $request->validate([
            'puesto' => 'required|string|max:100',
            'insignia_id' => 'nullable|exists:insignias,id',
            'tramo' => 'nullable|string|max:50',
            'donativo_pagado' => 'required|numeric|min:0',
            'estado' => 'required|in:Solicitada,Emitida,Anulada',
            'notas' => 'nullable|string|max:1000',
        ]);

        $papeleta->update($data);

        return back()->with('status', 'Papeleta actualizada correctamente.');
    }

    public function destroy(PapeletaSitio $papeleta): RedirectResponse
    {
        $papeleta->delete();

        return back()->with('status', 'Papeleta eliminada.');
    }

    public function toggleAsistencia(PapeletaSitio $papeleta): JsonResponse
    {
        $papeleta->update(['asistencia' => ! $papeleta->asistencia]);

        return response()->json(['asistencia' => $papeleta->asistencia]);
    }
}
