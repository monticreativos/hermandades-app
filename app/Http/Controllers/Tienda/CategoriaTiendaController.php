<?php

namespace App\Http\Controllers\Tienda;

use App\Http\Controllers\Controller;
use App\Models\CategoriaTienda;
use App\Models\ProductoTienda;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CategoriaTiendaController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:80', 'unique:categorias_tienda,nombre'],
        ]);

        $orden = (int) CategoriaTienda::query()->max('orden') + 1;
        CategoriaTienda::query()->create([
            'nombre' => trim($data['nombre']),
            'orden' => $orden,
            'activa' => true,
        ]);

        return back()->with('status', 'Categoría creada.');
    }

    public function update(Request $request, CategoriaTienda $categoriaTienda): RedirectResponse
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:80', 'unique:categorias_tienda,nombre,'.$categoriaTienda->id],
            'activa' => ['sometimes', 'boolean'],
        ]);

        $categoriaTienda->update([
            'nombre' => trim($data['nombre']),
            'activa' => $request->boolean('activa', true),
        ]);

        return back()->with('status', 'Categoría actualizada.');
    }

    public function destroy(CategoriaTienda $categoriaTienda): RedirectResponse
    {
        $enUso = ProductoTienda::query()->where('categoria', $categoriaTienda->nombre)->exists();
        if ($enUso) {
            return back()->with('error', 'No puede eliminarla: hay productos usando esta categoría.');
        }

        $categoriaTienda->delete();

        return back()->with('status', 'Categoría eliminada.');
    }
}
