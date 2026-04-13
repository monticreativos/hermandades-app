<?php

namespace App\Http\Controllers;

use App\Models\Cuadrilla;
use App\Models\RelevoCuadrilla;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

class CuadrillaRelevoPdfController extends Controller
{
    public function __invoke(Cuadrilla $cuadrilla, RelevoCuadrilla $relevo): Response
    {
        abort_if($relevo->cuadrilla_id !== $cuadrilla->id, 404);
        $relevo->load('detalles.hermano');

        $pdf = Pdf::loadView('cuadrillas.pdf.relevo', [
            'cuadrilla' => $cuadrilla,
            'relevo' => $relevo,
        ])->setPaper('A4', 'portrait');

        return $pdf->stream('cuadrante_relevo_'.$cuadrilla->paso.'_'.$relevo->fecha_salida?->format('Ymd').'.pdf');
    }
}
