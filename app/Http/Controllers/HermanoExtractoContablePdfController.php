<?php

namespace App\Http\Controllers;

use App\Models\ConfiguracionHermandad;
use App\Models\Hermano;
use App\Services\Informes\ExtractoContableHermanoPdfService;
use Illuminate\Http\Response;

class HermanoExtractoContablePdfController extends Controller
{
    public function __invoke(Hermano $hermano, ExtractoContableHermanoPdfService $extractoPdf): Response
    {
        $hermandad = ConfiguracionHermandad::query()->firstOrFail();
        $g = $extractoPdf->generar($hermano, $hermandad);

        return $g['pdf']->stream($g['nombre_archivo']);
    }
}
