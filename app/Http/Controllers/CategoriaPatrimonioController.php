<?php

namespace App\Http\Controllers;

use App\Models\CategoriaPatrimonio;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CategoriaPatrimonioController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:120', 'unique:categorias_patrimonio,nombre'],
        ]);

        CategoriaPatrimonio::query()->create($data);

        return back()->with('status', 'Categoría creada correctamente.');
    }

    public function update(Request $request, CategoriaPatrimonio $categoriaPatrimonio): RedirectResponse
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:120', 'unique:categorias_patrimonio,nombre,'.$categoriaPatrimonio->id],
        ]);

        $categoriaPatrimonio->update($data);

        return back()->with('status', 'Categoría actualizada correctamente.');
    }

    public function destroy(CategoriaPatrimonio $categoriaPatrimonio): RedirectResponse
    {
        if ($categoriaPatrimonio->enseres()->exists()) {
            return back()->with('error', 'No se puede eliminar: hay enseres asociados a esta categoría.');
        }

        $categoriaPatrimonio->delete();

        return back()->with('status', 'Categoría eliminada correctamente.');
    }
}
