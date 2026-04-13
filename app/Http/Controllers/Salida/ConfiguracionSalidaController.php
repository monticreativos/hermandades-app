<?php

namespace App\Http\Controllers\Salida;

use App\Http\Controllers\Controller;
use App\Models\ConfiguracionSalida;
use App\Models\Ejercicio;
use App\Models\Insignia;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ConfiguracionSalidaController extends Controller
{
    public function index(Request $request): View
    {
        $ejercicioActual = Ejercicio::query()
            ->where('estado', Ejercicio::ESTADO_ABIERTO)
            ->orderByDesc('año')
            ->first();

        $ejercicios = Ejercicio::query()->orderByDesc('año')->get();

        $añosEjercicios = $ejercicios->pluck('año')->unique()->sortDesc()->values()->all();
        $añosConConfig = ConfiguracionSalida::query()->orderByDesc('año')->pluck('año')->all();
        $añosSelector = collect($añosEjercicios)
            ->merge($añosConConfig)
            ->push((int) now()->year)
            ->unique()
            ->sortDesc()
            ->values()
            ->all();

        $añoSolicitado = $request->integer('año');
        $añoEditar = $añoSolicitado > 0
            ? $añoSolicitado
            : ($ejercicioActual?->año ?? (int) now()->year);

        $config = ConfiguracionSalida::query()
            ->where('año', $añoEditar)
            ->first();

        $historial = ConfiguracionSalida::query()
            ->orderByDesc('año')
            ->get();

        $insignias = Insignia::query()->orderBy('orden')->get();

        return view('salida.configuracion.index', [
            'config' => $config,
            'ejercicioActual' => $ejercicioActual,
            'ejercicios' => $ejercicios,
            'añoEditar' => $añoEditar,
            'añosSelector' => $añosSelector,
            'historial' => $historial,
            'insignias' => $insignias,
        ]);
    }

    public function guardar(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'año' => 'required|integer|min:2000|max:2100',
            'fecha_salida' => 'nullable|date',
            'donativo_defecto' => 'required|numeric|min:0',
            'fecha_inicio_reparto' => 'nullable|date',
            'fecha_fin_reparto' => array_filter([
                'nullable',
                'date',
                $request->filled('fecha_inicio_reparto') ? 'after_or_equal:fecha_inicio_reparto' : null,
            ]),
            'notas' => 'nullable|string|max:2000',
        ]);

        $data['activa'] = $request->boolean('activa');

        ConfiguracionSalida::updateOrCreate(
            ['año' => $data['año']],
            $data
        );

        return redirect()
            ->route('salida.configuracion.index', ['año' => $data['año']])
            ->with('status', 'Configuración de salida guardada para el año '.$data['año'].'. Los datos se conservan en la base de datos; puede consultar años anteriores en el historial.');
    }
}
