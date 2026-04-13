<?php

namespace App\Http\Controllers;

use App\Models\Familia;
use App\Models\Hermano;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class HermanoFamiliaController extends Controller
{
    public function store(Request $request, Hermano $hermano): RedirectResponse
    {
        $data = $request->validate([
            'familiar_id' => ['required', 'integer', 'exists:hermanos,id'],
            'parentesco' => ['required', 'in:Padre,Madre,Hijo/a,Cónyuge,Tutor'],
        ]);

        if ((int) $data['familiar_id'] === (int) $hermano->id) {
            return back()->withErrors(['familiar_id' => 'No puedes agregarte como familiar de ti mismo.']);
        }

        $familia = $hermano->familias()->first();
        if (! $familia) {
            $familia = Familia::query()->create([
                'nombre' => 'Familia '.$hermano->apellidos,
                'pago_unificado' => false,
            ]);
            $familia->miembros()->syncWithoutDetaching([$hermano->id => ['parentesco' => 'Tutor']]);
        }

        $familia->miembros()->syncWithoutDetaching([
            (int) $data['familiar_id'] => ['parentesco' => $data['parentesco']],
        ]);

        return back()->with('status', 'Familiar añadido correctamente.');
    }

    public function configurar(Request $request, Hermano $hermano): RedirectResponse
    {
        $data = $request->validate([
            'es_cabeza_familia' => ['nullable', 'boolean'],
            'beneficiario_fiscal_hermano_id' => ['nullable', 'integer', 'exists:hermanos,id'],
            'pago_unificado' => ['nullable', 'boolean'],
            'pagador_hermano_id' => ['nullable', 'integer', 'exists:hermanos,id'],
        ]);

        $familia = $hermano->familias()->first();
        if (! $familia) {
            $familia = Familia::query()->create([
                'nombre' => 'Familia '.$hermano->apellidos,
                'pago_unificado' => false,
            ]);
            $familia->miembros()->syncWithoutDetaching([$hermano->id => ['parentesco' => 'Tutor']]);
        }

        $miembrosIds = $familia->miembros()->pluck('hermanos.id')->all();
        $beneficiario = $data['beneficiario_fiscal_hermano_id'] ?? null;
        if ($beneficiario && ! in_array((int) $beneficiario, $miembrosIds, true)) {
            return back()->withErrors(['beneficiario_fiscal_hermano_id' => 'El beneficiario fiscal debe pertenecer a la misma familia.']);
        }

        $pagador = $data['pagador_hermano_id'] ?? null;
        if ($pagador && ! in_array((int) $pagador, $miembrosIds, true)) {
            return back()->withErrors(['pagador_hermano_id' => 'El pagador unificado debe pertenecer a la misma familia.']);
        }

        $hermano->update([
            'es_cabeza_familia' => (bool) ($data['es_cabeza_familia'] ?? false),
            'beneficiario_fiscal_hermano_id' => $beneficiario,
        ]);

        $familia->update([
            'pago_unificado' => (bool) ($data['pago_unificado'] ?? false),
            'pagador_hermano_id' => $pagador,
        ]);

        if ($hermano->fecha_nacimiento && $hermano->fecha_nacimiento->age < 18) {
            $tutor = $familia->miembros()
                ->wherePivotIn('parentesco', ['Padre', 'Madre', 'Tutor'])
                ->where('hermanos.id', '!=', $hermano->id)
                ->exists();
            if (! $tutor) {
                return back()->withErrors(['familia' => 'El hermano es menor de edad: debe vincularse al menos un Padre/Madre/Tutor en la unidad familiar.']);
            }
        }

        return back()->with('status', 'Configuración familiar y fiscal actualizada.');
    }
}
