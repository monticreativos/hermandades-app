<?php

namespace App\Http\Controllers;

use App\Models\Banco;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BancoController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:120', 'unique:bancos,nombre'],
            'codigo' => ['nullable', 'string', 'max:20'],
        ]);

        Banco::create($data);

        return back()->with('status', 'Banco creado correctamente.');
    }

    public function update(Request $request, Banco $banco): RedirectResponse
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:120', 'unique:bancos,nombre,'.$banco->id],
            'codigo' => ['nullable', 'string', 'max:20'],
        ]);

        $banco->update($data);

        return back()->with('status', 'Banco actualizado correctamente.');
    }

    public function destroy(Banco $banco): RedirectResponse
    {
        $banco->delete();

        return back()->with('status', 'Banco eliminado correctamente.');
    }
}
