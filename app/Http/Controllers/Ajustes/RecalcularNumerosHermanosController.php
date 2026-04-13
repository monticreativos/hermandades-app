<?php

namespace App\Http\Controllers\Ajustes;

use App\Http\Controllers\Controller;
use App\Http\Requests\RecalcularNumerosHermanosRequest;
use App\Models\Actividad;
use App\Services\Hermanos\RenumeracionHermanosService;
use App\Support\RegistroActividad;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class RecalcularNumerosHermanosController extends Controller
{
    public function show(): View
    {
        return view('ajustes.renumeracion-hermanos');
    }

    public function store(
        RecalcularNumerosHermanosRequest $request,
        RenumeracionHermanosService $renumeracionHermanosService
    ): RedirectResponse {
        try {
            $total = $renumeracionHermanosService->recalcularSinHuecos();
        } catch (\RuntimeException $e) {
            return redirect()
                ->route('ajustes.renumeracion.show')
                ->with('error', $e->getMessage());
        }

        RegistroActividad::registrar(
            Actividad::ACCION_RENUMERAR_HERMANOS,
            'Recálculo de números de hermano sin huecos: '.$total.' fichas reordenadas por fecha de alta (irreversible).'
        );

        return redirect()
            ->route('ajustes.index')
            ->with('status', 'Números de hermano recalculados: '.$total.' registros reordenados por antigüedad de alta.');
    }
}
