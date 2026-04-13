<?php

namespace App\Http\Controllers\Salida;

use App\Http\Controllers\Controller;
use App\Models\Tunica;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TunicaController extends Controller
{
    public function index(Request $request): View
    {
        $tunicas = Tunica::query()
            ->with('hermano')
            ->when($request->filled('estado'), fn ($q) => $q->where('estado', $request->string('estado')))
            ->when($request->filled('talla'), fn ($q) => $q->where('talla', $request->string('talla')))
            ->when($request->filled('q'), function ($q) use ($request): void {
                $search = $request->string('q')->toString();
                $q->where(function ($sub) use ($search): void {
                    $sub->where('codigo', 'like', "%{$search}%")
                        ->orWhereHas('hermano', fn ($h) => $h->where('nombre', 'like', "%{$search}%")
                            ->orWhere('apellidos', 'like', "%{$search}%"));
                });
            })
            ->orderBy('codigo')
            ->paginate(50)
            ->withQueryString();

        $tallas = Tunica::query()->distinct()->orderBy('talla')->pluck('talla');

        $stats = [
            'total' => Tunica::count(),
            'disponibles' => Tunica::where('estado', 'Disponible')->count(),
            'prestadas' => Tunica::where('estado', 'Prestada')->count(),
            'reparacion' => Tunica::where('estado', 'En reparación')->count(),
        ];

        return view('salida.tunicas.index', [
            'tunicas' => $tunicas,
            'tallas' => $tallas,
            'stats' => $stats,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'codigo' => 'required|string|max:50|unique:tunicas,codigo',
            'talla' => 'required|string|max:20',
            'estado' => 'required|in:Disponible,Prestada,En reparación,Baja',
            'hermano_id' => 'nullable|exists:hermanos,id',
            'fianza' => 'nullable|numeric|min:0',
            'fecha_prestamo' => 'nullable|date',
            'notas' => 'nullable|string|max:1000',
        ]);

        Tunica::create($data);

        return back()->with('status', 'Túnica registrada.');
    }

    public function update(Request $request, Tunica $tunica): RedirectResponse
    {
        $data = $request->validate([
            'codigo' => 'required|string|max:50|unique:tunicas,codigo,'.$tunica->id,
            'talla' => 'required|string|max:20',
            'estado' => 'required|in:Disponible,Prestada,En reparación,Baja',
            'hermano_id' => 'nullable|exists:hermanos,id',
            'fianza' => 'nullable|numeric|min:0',
            'fecha_prestamo' => 'nullable|date',
            'fecha_devolucion' => 'nullable|date',
            'notas' => 'nullable|string|max:1000',
        ]);

        $tunica->update($data);

        return back()->with('status', 'Túnica actualizada.');
    }

    public function destroy(Tunica $tunica): RedirectResponse
    {
        $tunica->delete();

        return back()->with('status', 'Túnica eliminada.');
    }
}
