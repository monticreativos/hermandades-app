<?php

namespace App\Http\Controllers;

use App\Models\CostaleroPerfil;
use App\Models\Cuadrilla;
use App\Models\CuadrillaAviso;
use App\Models\EnsayoAsistencia;
use App\Models\EnsayoCuadrilla;
use App\Models\Hermano;
use App\Models\RelevoCuadrilla;
use App\Models\RelevoDetalle;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CuadrillaController extends Controller
{
    public function index(Request $request): View
    {
        $year = (int) $request->get('año', (int) now()->format('Y'));
        $cuadrillas = Cuadrilla::query()
            ->withCount('costaleros')
            ->with('capataz')
            ->where('año', $year)
            ->orderBy('paso')
            ->orderBy('nombre')
            ->get();

        return view('cuadrillas.index', ['cuadrillas' => $cuadrillas, 'año' => $year]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'año' => ['required', 'integer', 'min:2000', 'max:2100'],
            'nombre' => ['required', 'string', 'max:120'],
            'paso' => ['required', 'in:cristo,virgen'],
            'numero_trabajaderas' => ['required', 'integer', 'min:1', 'max:20'],
            'puestos_por_trabajadera' => ['required', 'integer', 'min:2', 'max:8'],
            'notas' => ['nullable', 'string', 'max:2000'],
        ]);
        $data['capataz_user_id'] = Auth::id();
        Cuadrilla::query()->create($data);

        return back()->with('status', 'Cuadrilla creada.');
    }

    public function iguala(Cuadrilla $cuadrilla): View
    {
        $perfiles = CostaleroPerfil::query()
            ->with('hermano')
            ->where('cuadrilla_id', $cuadrilla->id)
            ->orderByDesc('altura_cm')
            ->get();

        $hermanosSinPerfil = Hermano::query()
            ->where('estado', 'Alta')
            ->whereDoesntHave('costaleroPerfil')
            ->orderBy('numero_hermano')
            ->limit(300)
            ->get();

        return view('cuadrillas.iguala', compact('cuadrilla', 'perfiles', 'hermanosSinPerfil'));
    }

    public function asignarCostalero(Request $request, Cuadrilla $cuadrilla): RedirectResponse
    {
        $data = $request->validate([
            'hermano_id' => ['required', 'exists:hermanos,id'],
            'altura_cm' => ['nullable', 'integer', 'min:130', 'max:230'],
            'calzado_talla' => ['nullable', 'integer', 'min:30', 'max:55'],
            'ropa_talla' => ['nullable', 'string', 'max:16'],
            'trabajadera_numero' => ['nullable', 'integer', 'min:1', 'max:20'],
            'palo' => ['nullable', 'in:costero_izquierdo,costero_derecho,fijador,corriente'],
            'alergias' => ['nullable', 'string', 'max:2000'],
            'lesiones' => ['nullable', 'string', 'max:2000'],
            'anios_cuadrilla' => ['nullable', 'integer', 'min:0', 'max:80'],
        ]);

        CostaleroPerfil::query()->updateOrCreate(
            ['hermano_id' => $data['hermano_id']],
            [
                'cuadrilla_id' => $cuadrilla->id,
                'altura_cm' => $data['altura_cm'] ?? null,
                'calzado_talla' => $data['calzado_talla'] ?? null,
                'ropa_talla' => $data['ropa_talla'] ?? null,
                'trabajadera_numero' => $data['trabajadera_numero'] ?? null,
                'palo' => $data['palo'] ?? null,
                'alergias' => $data['alergias'] ?? null,
                'lesiones' => $data['lesiones'] ?? null,
                'anios_cuadrilla' => $data['anios_cuadrilla'] ?? 0,
            ]
        );

        return back()->with('status', 'Costalero asignado/actualizado.');
    }

    public function ensayos(Cuadrilla $cuadrilla): View
    {
        $ensayos = EnsayoCuadrilla::query()
            ->withCount(['asistencias as ausencias_count' => fn ($q) => $q->where('asistio', false)])
            ->where('cuadrilla_id', $cuadrilla->id)
            ->orderByDesc('fecha')
            ->get();

        $costaleros = CostaleroPerfil::query()
            ->with('hermano')
            ->where('cuadrilla_id', $cuadrilla->id)
            ->orderBy('trabajadera_numero')
            ->orderBy('palo')
            ->get();

        $faltasPorHermano = EnsayoAsistencia::query()
            ->selectRaw('hermano_id, SUM(CASE WHEN asistio = 0 THEN 1 ELSE 0 END) as faltas')
            ->whereIn('ensayo_id', EnsayoCuadrilla::query()->where('cuadrilla_id', $cuadrilla->id)->pluck('id'))
            ->groupBy('hermano_id')
            ->pluck('faltas', 'hermano_id');

        return view('cuadrillas.ensayos', compact('cuadrilla', 'ensayos', 'costaleros', 'faltasPorHermano'));
    }

    public function storeEnsayo(Request $request, Cuadrilla $cuadrilla): RedirectResponse
    {
        $data = $request->validate([
            'fecha' => ['required', 'date'],
            'hora_inicio' => ['nullable', 'date_format:H:i'],
            'lugar' => ['nullable', 'string', 'max:255'],
            'notas' => ['nullable', 'string', 'max:2000'],
        ]);
        $ensayo = EnsayoCuadrilla::query()->create(['cuadrilla_id' => $cuadrilla->id] + $data);

        foreach ($cuadrilla->costaleros()->pluck('hermano_id') as $hid) {
            EnsayoAsistencia::query()->create([
                'ensayo_id' => $ensayo->id,
                'hermano_id' => $hid,
                'asistio' => false,
            ]);
        }

        return back()->with('status', 'Ensayo creado.');
    }

    public function marcarAsistencia(Request $request, Cuadrilla $cuadrilla, EnsayoCuadrilla $ensayo): RedirectResponse
    {
        abort_if($ensayo->cuadrilla_id !== $cuadrilla->id, 404);
        $rows = $request->validate([
            'asistencias' => ['array'],
            'asistencias.*' => ['nullable', 'boolean'],
        ]);

        $items = $rows['asistencias'] ?? [];
        foreach ($ensayo->asistencias as $a) {
            $a->asistio = isset($items[$a->hermano_id]);
            $a->save();
        }

        return back()->with('status', 'Asistencia registrada.');
    }

    public function relevos(Cuadrilla $cuadrilla): View
    {
        $relevos = RelevoCuadrilla::query()
            ->with(['detalles.hermano'])
            ->where('cuadrilla_id', $cuadrilla->id)
            ->orderByDesc('fecha_salida')
            ->get();
        $costaleros = $cuadrilla->costaleros()->with('hermano')->get();

        return view('cuadrillas.relevos', compact('cuadrilla', 'relevos', 'costaleros'));
    }

    public function storeRelevo(Request $request, Cuadrilla $cuadrilla): RedirectResponse
    {
        $data = $request->validate([
            'titulo' => ['required', 'string', 'max:255'],
            'fecha_salida' => ['required', 'date'],
            'notas' => ['nullable', 'string', 'max:2000'],
        ]);
        RelevoCuadrilla::query()->create(['cuadrilla_id' => $cuadrilla->id] + $data);

        return back()->with('status', 'Cuadrante de relevo creado.');
    }

    public function addDetalleRelevo(Request $request, Cuadrilla $cuadrilla, RelevoCuadrilla $relevo): RedirectResponse
    {
        abort_if($relevo->cuadrilla_id !== $cuadrilla->id, 404);
        $data = $request->validate([
            'punto' => ['required', 'string', 'max:255'],
            'hora_desde' => ['nullable', 'date_format:H:i'],
            'hora_hasta' => ['nullable', 'date_format:H:i'],
            'turno' => ['nullable', 'string', 'max:64'],
            'hermano_id' => ['nullable', 'exists:hermanos,id'],
            'notas' => ['nullable', 'string', 'max:1000'],
        ]);
        RelevoDetalle::query()->create(['relevo_id' => $relevo->id] + $data);

        return back()->with('status', 'Detalle de relevo añadido.');
    }

    public function avisos(Cuadrilla $cuadrilla): View
    {
        $avisos = CuadrillaAviso::query()->where('cuadrilla_id', $cuadrilla->id)->latest()->get();

        return view('cuadrillas.avisos', compact('cuadrilla', 'avisos'));
    }

    public function storeAviso(Request $request, Cuadrilla $cuadrilla): RedirectResponse
    {
        $data = $request->validate([
            'titulo' => ['required', 'string', 'max:255'],
            'mensaje' => ['required', 'string', 'max:5000'],
        ]);
        CuadrillaAviso::query()->create([
            'cuadrilla_id' => $cuadrilla->id,
            'user_id' => Auth::id(),
            'titulo' => $data['titulo'],
            'mensaje' => $data['mensaje'],
            'enviado_en' => now(),
        ]);

        return back()->with('status', 'Aviso enviado a la cuadrilla.');
    }
}
