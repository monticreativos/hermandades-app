<?php

namespace App\Http\Controllers\Salida;

use App\Http\Controllers\Controller;
use App\Models\ConfiguracionHermandad;
use App\Models\ConfiguracionSalida;
use App\Models\Ejercicio;
use App\Models\PapeletaSitio;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

class PapeletaPdfController extends Controller
{
    public function papeleta(PapeletaSitio $papeleta): Response
    {
        $papeleta->load(['hermano', 'ejercicio', 'insignia']);

        $hermandad = ConfiguracionHermandad::query()->first();
        $config = ConfiguracionSalida::query()
            ->where('año', $papeleta->ejercicio->año)
            ->first();

        $escudoPath = null;
        if ($hermandad?->escudo_path && ! str_starts_with((string) $hermandad->escudo_path, 'http')) {
            $rel = ltrim((string) $hermandad->escudo_path, '/');
            $fullPath = storage_path('app/public/'.$rel);
            if (file_exists($fullPath)) {
                $escudoPath = $fullPath;
            }
        }

        $pdf = Pdf::loadView('salida.pdf.papeleta', [
            'papeleta' => $papeleta,
            'hermandad' => $hermandad,
            'config' => $config,
            'escudoPath' => $escudoPath,
        ]);

        $pdf->setPaper('A5', 'portrait');

        $nombre = 'papeleta_'.$papeleta->hermano->numero_hermano.'_'.$papeleta->ejercicio->año.'.pdf';

        return $pdf->stream($nombre);
    }

    public function listadoCortejo(int $ejercicioId): Response
    {
        $ejercicio = Ejercicio::findOrFail($ejercicioId);

        $papeletas = PapeletaSitio::query()
            ->with(['hermano', 'insignia'])
            ->where('papeletas_sitio.ejercicio_id', $ejercicioId)
            ->where('papeletas_sitio.estado', '!=', 'Anulada')
            ->join('hermanos', 'papeletas_sitio.hermano_id', '=', 'hermanos.id')
            ->orderBy('papeletas_sitio.tramo')
            ->orderBy('hermanos.numero_hermano')
            ->select('papeletas_sitio.*')
            ->get();

        $tramosAgrupados = $papeletas->groupBy('tramo');

        $hermandad = ConfiguracionHermandad::query()->first();

        $pdf = Pdf::loadView('salida.pdf.listado-cortejo', [
            'ejercicio' => $ejercicio,
            'tramosAgrupados' => $tramosAgrupados,
            'hermandad' => $hermandad,
            'total' => $papeletas->count(),
        ]);

        $pdf->setPaper('A4', 'portrait');

        return $pdf->stream('listado_cortejo_'.$ejercicio->año.'.pdf');
    }
}
