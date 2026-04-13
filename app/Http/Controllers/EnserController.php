<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEnserRequest;
use App\Http\Requests\UpdateEnserRequest;
use App\Models\CategoriaPatrimonio;
use App\Models\Enser;
use App\Models\EstadoConservacionPatrimonio;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class EnserController extends Controller
{
    public function galeria(): View
    {
        $query = Enser::query()
            ->with(['categoriaPatrimonio', 'estadoConservacionPatrimonio', 'fotos'])
            ->orderBy('nombre');

        if (request()->filled('categoria_id')) {
            $query->where('categoria_id', (int) request('categoria_id'));
        }
        if (request()->filled('estado_conservacion_id')) {
            $query->where('estado_conservacion_id', (int) request('estado_conservacion_id'));
        }
        if (request()->filled('tipo_ubicacion')) {
            $query->where('tipo_ubicacion', (string) request('tipo_ubicacion'));
        }
        if (request()->filled('q')) {
            $search = trim((string) request('q'));
            $query->where(function ($q) use ($search): void {
                $q->where('nombre', 'like', "%{$search}%")
                    ->orWhere('autor', 'like', "%{$search}%")
                    ->orWhere('material_tecnica', 'like', "%{$search}%")
                    ->orWhere('materiales', 'like', "%{$search}%")
                    ->orWhere('numero_inventario', 'like', "%{$search}%");
            });
        }

        $enseres = $query->paginate(18)->withQueryString();

        return view('patrimonio.galeria', [
            'enseres' => $enseres,
            'categorias' => CategoriaPatrimonio::query()->orderBy('nombre')->get(),
            'estadosConservacion' => EstadoConservacionPatrimonio::query()->orderBy('nombre')->get(),
            'tiposUbicacion' => ['Estantería', 'Vitrina', 'Almacén', 'Cedido'],
        ]);
    }

    public function index(): View
    {
        $query = Enser::query()->with(['categoriaPatrimonio', 'estadoConservacionPatrimonio']);

        if (request()->filled('categoria_id')) {
            $query->where('categoria_id', (int) request('categoria_id'));
        }

        if (request()->filled('ubicacion')) {
            $query->where('ubicacion', request('ubicacion'));
        }

        if (request()->filled('estado_conservacion_id')) {
            $query->where('estado_conservacion_id', (int) request('estado_conservacion_id'));
        }

        if (request()->filled('q')) {
            $search = trim((string) request('q'));
            $query->where(function ($q) use ($search): void {
                $q->where('nombre', 'like', "%{$search}%")
                    ->orWhere('autor', 'like', "%{$search}%")
                    ->orWhere('materiales', 'like', "%{$search}%")
                    ->orWhere('ubicacion', 'like', "%{$search}%");
            });
        }

        $enseres = $query->orderBy('nombre')->paginate(15)->withQueryString();
        $categorias = CategoriaPatrimonio::query()->orderBy('nombre')->get();
        $estadosConservacion = EstadoConservacionPatrimonio::query()->orderBy('nombre')->get();
        $defaultEstadoConservacionId = $estadosConservacion->firstWhere('nombre', 'Bueno')?->id
            ?? $estadosConservacion->first()?->id;
        $ubicaciones = Enser::query()
            ->whereNotNull('ubicacion')
            ->where('ubicacion', '!=', '')
            ->distinct()
            ->orderBy('ubicacion')
            ->pluck('ubicacion');

        return view('patrimonio.index', [
            'enseres' => $enseres,
            'categorias' => $categorias,
            'estadosConservacion' => $estadosConservacion,
            'defaultEstadoConservacionId' => $defaultEstadoConservacionId,
            'ubicaciones' => $ubicaciones,
            'enseresJson' => $enseres->getCollection()->map(fn (Enser $e): array => [
                'id' => $e->id,
                'nombre' => $e->nombre,
                'categoria_id' => $e->categoria_id,
                'ubicacion' => $e->ubicacion,
                'autor' => $e->autor,
                'año_creacion' => $e->año_creacion,
                'materiales' => $e->materiales,
                'estado_conservacion_id' => $e->estado_conservacion_id,
                'estado_nombre' => $e->estadoConservacionPatrimonio?->nombre,
                'valor_estimado' => $e->valor_estimado,
                'descripcion_detallada' => $e->descripcion_detallada,
                'ultima_revision' => optional($e->ultima_revision)->format('Y-m-d'),
            ])->values(),
        ]);
    }

    public function store(StoreEnserRequest $request): RedirectResponse
    {
        $data = $request->safe()->except(['imagen_principal'])->toArray();
        if (($data['valor_estimado'] ?? null) === '' || ($data['valor_estimado'] ?? null) === null) {
            $data['valor_estimado'] = null;
        }

        $enser = Enser::create($data);

        if ($request->hasFile('imagen_principal')) {
            $enser->imagen_principal_path = $request->file('imagen_principal')
                ->store("patrimonio/enser/{$enser->id}", 'public');
            $enser->save();
        }

        return redirect()
            ->route('patrimonio.index')
            ->with('status', 'Enser registrado correctamente.');
    }

    public function show(Enser $enser): View
    {
        $enser->load(['categoriaPatrimonio', 'estadoConservacionPatrimonio']);

        return view('patrimonio.show', compact('enser'));
    }

    public function update(UpdateEnserRequest $request, Enser $enser): RedirectResponse
    {
        $data = $request->safe()->except(['imagen_principal', 'edit_enser_id'])->toArray();
        if (($data['valor_estimado'] ?? null) === '' || ($data['valor_estimado'] ?? null) === null) {
            $data['valor_estimado'] = null;
        }

        $enser->update($data);

        if ($request->hasFile('imagen_principal')) {
            if ($enser->imagen_principal_path) {
                Storage::disk('public')->delete($enser->imagen_principal_path);
            }
            $enser->imagen_principal_path = $request->file('imagen_principal')
                ->store("patrimonio/enser/{$enser->id}", 'public');
            $enser->save();
        }

        return redirect()
            ->route('patrimonio.index')
            ->with('status', 'Enser actualizado correctamente.');
    }

    public function destroy(Enser $enser): RedirectResponse
    {
        if ($enser->imagen_principal_path) {
            Storage::disk('public')->delete($enser->imagen_principal_path);
        }
        $enser->delete();

        return redirect()
            ->route('patrimonio.index')
            ->with('status', 'Enser eliminado correctamente.');
    }
}
