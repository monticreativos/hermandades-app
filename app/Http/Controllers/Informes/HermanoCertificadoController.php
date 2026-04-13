<?php

namespace App\Http\Controllers\Informes;

use App\Http\Controllers\Controller;
use App\Models\ConfiguracionHermandad;
use App\Models\Hermano;
use App\Services\Informes\CertificadoHermanoService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class HermanoCertificadoController extends Controller
{
    public function __construct(
        private readonly CertificadoHermanoService $certificadoHermanoService
    ) {}

    public function pertenencia(Hermano $hermano): Response
    {
        $hermandad = ConfiguracionHermandad::query()->firstOrFail();
        $datos = $this->certificadoHermanoService->datosPertenencia($hermano, $hermandad);

        $pdf = Pdf::loadView('informes.pdf.certificado-pertenencia', $datos);
        $pdf->setPaper('A4', 'portrait');

        $slug = 'certificado_pertenencia_'.preg_replace('/\W+/', '_', (string) $hermano->numero_hermano).'.pdf';

        return $pdf->stream($slug);
    }

    public function cuotasHacienda(Request $request, Hermano $hermano): Response
    {
        $hermandad = ConfiguracionHermandad::query()->firstOrFail();
        $año = (int) $request->query('año', (string) now()->year);
        $año = max(2000, min((int) now()->year + 1, $año));

        $hermano->loadMissing('cuotaPendienteEjercicio');

        $datos = $this->certificadoHermanoService->datosCuotasHacienda($hermano, $hermandad, $año);

        $pdf = Pdf::loadView('informes.pdf.certificado-cuotas-hacienda', $datos);
        $pdf->setPaper('A4', 'portrait');

        $slug = 'certificado_cuotas_'.$año.'_'.preg_replace('/\W+/', '_', (string) $hermano->numero_hermano).'.pdf';

        return $pdf->stream($slug);
    }
}
