<?php

namespace App\Http\Controllers\Informes;

use App\Exports\HermanosListadoPersonalizadoExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\ExportListadoHermanosRequest;
use App\Models\Hermano;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ListadosPersonalizadosController extends Controller
{
    public function index(): View
    {
        return view('informes.listados.index');
    }

    public function export(ExportListadoHermanosRequest $request): BinaryFileResponse|RedirectResponse
    {
        $estado = $request->string('estado')->toString();
        $columnas = $request->input('columnas', []);
        if (! is_array($columnas)) {
            $columnas = [];
        }

        $q = Hermano::query()->orderBy('numero_hermano');
        if ($estado !== '' && $estado !== 'todos') {
            $q->where('estado', $estado);
        }

        $hermanos = $q->get();
        if ($hermanos->isEmpty()) {
            return redirect()
                ->route('informes.listados.index')
                ->with('error', 'No hay hermanos que coincidan con el filtro seleccionado.');
        }

        $nombre = 'listado_hermanos_'.now()->format('Ymd_His').'.xlsx';

        return Excel::download(
            new HermanosListadoPersonalizadoExport($hermanos, $columnas),
            $nombre
        );
    }
}
