<?php

namespace App\Http\Controllers;

use App\Models\EstadoConservacionPatrimonio;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class EstadoConservacionPatrimonioController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:120', 'unique:estados_conservacion_patrimonio,nombre'],
        ]);

        EstadoConservacionPatrimonio::query()->create($data);

        return back()->with('status', 'Estado de conservación creado correctamente.');
    }

    public function update(Request $request, EstadoConservacionPatrimonio $estadoConservacionPatrimonio): RedirectResponse
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:120', 'unique:estados_conservacion_patrimonio,nombre,'.$estadoConservacionPatrimonio->id],
        ]);

        $estadoConservacionPatrimonio->update($data);

        return back()->with('status', 'Estado de conservación actualizado correctamente.');
    }

    public function destroy(EstadoConservacionPatrimonio $estadoConservacionPatrimonio): RedirectResponse
    {
        if ($estadoConservacionPatrimonio->enseres()->exists()) {
            return back()->with('error', 'No se puede eliminar: hay enseres con este estado de conservación.');
        }

        $estadoConservacionPatrimonio->delete();

        return back()->with('status', 'Estado de conservación eliminado correctamente.');
    }
}
