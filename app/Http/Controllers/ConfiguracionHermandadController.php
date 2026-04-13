<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateConfiguracionHermandadRequest;
use App\Models\Actividad;
use App\Models\ConfiguracionHermandad;
use App\Support\RegistroActividad;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ConfiguracionHermandadController extends Controller
{
    public function index(): View
    {
        $configuracion = ConfiguracionHermandad::query()->firstOrCreate(
            ['id' => 1],
            [
                'nombre_hermandad' => 'Mi Hermandad',
                'nombre_corto' => 'Mi Hermandad',
            ]
        );

        return view('ajustes.index', compact('configuracion'));
    }

    public function update(UpdateConfiguracionHermandadRequest $request): RedirectResponse
    {
        $configuracion = ConfiguracionHermandad::query()->firstOrCreate(
            ['id' => 1],
            [
                'nombre_hermandad' => 'Mi Hermandad',
                'nombre_corto' => 'Mi Hermandad',
            ]
        );

        $data = $request->safe()->except(['escudo', 'firma_secretario', 'firma_mayordomo', 'sello_hermandad']);

        if ($request->hasFile('escudo')) {
            if ($configuracion->escudo_path && ! str_starts_with($configuracion->escudo_path, 'http')) {
                Storage::disk('public')->delete($configuracion->escudo_path);
            }

            $data['escudo_path'] = $request->file('escudo')->store('hermandad/escudo', 'public');
        }

        foreach (
            [
                'firma_secretario' => 'firma_secretario_path',
                'firma_mayordomo' => 'firma_mayordomo_path',
                'sello_hermandad' => 'sello_hermandad_path',
            ] as $fileKey => $pathKey
        ) {
            if ($request->hasFile($fileKey)) {
                if ($configuracion->{$pathKey} && ! str_starts_with((string) $configuracion->{$pathKey}, 'http')) {
                    Storage::disk('public')->delete($configuracion->{$pathKey});
                }
                $data[$pathKey] = $request->file($fileKey)->store('hermandad/firmas', 'public');
            }
        }

        $configuracion->update($data);

        if ($request->hasFile('escudo')) {
            RegistroActividad::registrar(
                Actividad::ACCION_CAMBIO_ESCUDO,
                'Cambio o sustitución del escudo institucional de la Hermandad.'
            );
        }

        return redirect()
            ->route('ajustes.index')
            ->with('status', 'Datos de la hermandad actualizados correctamente.');
    }
}
