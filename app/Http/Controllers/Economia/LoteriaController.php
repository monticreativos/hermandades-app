<?php

namespace App\Http\Controllers\Economia;

use App\Http\Controllers\Controller;
use App\Models\Hermano;
use App\Models\Loteria;
use App\Models\LoteriaAsignacion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LoteriaController extends Controller
{
    public function index(): View
    {
        $loterias = Loteria::query()
            ->withSum('asignaciones', 'participaciones')
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('economia.loterias.index', compact('loterias'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'sorteo' => ['required', 'string', 'max:120'],
            'numero' => ['required', 'string', 'max:80'],
            'serie_fraccion' => ['nullable', 'string', 'max:120'],
            'total_participaciones' => ['required', 'integer', 'min:0'],
            'precio_participacion' => ['required', 'numeric', 'min:0', 'decimal:0,2'],
            'donativo' => ['nullable', 'numeric', 'min:0', 'decimal:0,2'],
            'observaciones' => ['nullable', 'string', 'max:2000'],
        ]);
        $data['donativo'] = $data['donativo'] ?? 0;

        Loteria::query()->create($data);

        return redirect()->route('economia.loterias.index')->with('status', 'Sorteo de lotería registrado.');
    }

    public function show(Loteria $loteria): View
    {
        $asignaciones = $loteria->asignaciones()
            ->with('hermano')
            ->orderBy('cobrado')
            ->orderBy('hermano_id')
            ->paginate(25);

        $hermanos = Hermano::query()
            ->where('estado', 'Alta')
            ->orderBy('numero_hermano')
            ->get(['id', 'numero_hermano', 'nombre', 'apellidos']);

        return view('economia.loterias.show', [
            'loteria' => $loteria,
            'asignaciones' => $asignaciones,
            'hermanos' => $hermanos,
            'disponibles' => $loteria->participacionesDisponibles(),
        ]);
    }

    public function storeAsignacion(Request $request, Loteria $loteria): RedirectResponse
    {
        $data = $request->validate([
            'hermano_id' => ['required', 'integer', 'exists:hermanos,id'],
            'participaciones' => ['required', 'integer', 'min:1'],
            'referencia_taco' => ['nullable', 'string', 'max:120'],
            'importe_a_cobrar' => ['nullable', 'numeric', 'min:0', 'decimal:0,2'],
            'notas' => ['nullable', 'string', 'max:500'],
        ]);

        $part = (int) $data['participaciones'];
        if ($part > $loteria->participacionesDisponibles()) {
            return back()->withInput()->with('error', 'No hay participaciones libres suficientes en este sorteo.');
        }

        $importe = $request->filled('importe_a_cobrar')
            ? round((float) $request->input('importe_a_cobrar'), 2)
            : null;
        if ($importe === null) {
            $base = round((float) $loteria->precio_participacion * $part, 2);
            $donativoPart = $loteria->total_participaciones > 0
                ? round((float) $loteria->donativo * $part / $loteria->total_participaciones, 2)
                : 0;
            $importe = round($base + $donativoPart, 2);
        }

        LoteriaAsignacion::query()->create([
            'loteria_id' => $loteria->id,
            'hermano_id' => (int) $data['hermano_id'],
            'participaciones' => $part,
            'referencia_taco' => $data['referencia_taco'] ?? null,
            'importe_a_cobrar' => $importe,
            'cobrado' => false,
            'notas' => $data['notas'] ?? null,
        ]);

        return back()->with('status', 'Taco asignado al hermano.');
    }

    public function toggleCobro(LoteriaAsignacion $asignacion): RedirectResponse
    {
        if ($asignacion->cobrado) {
            $asignacion->update([
                'cobrado' => false,
                'fecha_cobro' => null,
            ]);
        } else {
            $asignacion->update([
                'cobrado' => true,
                'fecha_cobro' => now()->toDateString(),
            ]);
        }

        return back()->with('status', 'Estado de cobro actualizado.');
    }
}
