<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\ConfiguracionHermandad;
use App\Services\Informes\CertificadoHermanoService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class PortalCertificadoHaciendaController extends Controller
{
    public function __construct(
        private readonly CertificadoHermanoService $certificadoHermanoService
    ) {}

    public function __invoke(Request $request): Response
    {
        $cuenta = Auth::guard('portal')->user();
        $hermano = $cuenta->hermano;

        $año = (int) $request->query('año', (string) now()->year);
        $año = max(2000, min((int) now()->year + 1, $año));

        $hermandad = ConfiguracionHermandad::query()->firstOrFail();
        $hermano->loadMissing('cuotaPendienteEjercicio');

        $datos = $this->certificadoHermanoService->datosCuotasHacienda($hermano, $hermandad, $año);

        $pdf = Pdf::loadView('informes.pdf.certificado-cuotas-hacienda', $datos);
        $pdf->setPaper('A4', 'portrait');

        $slug = 'certificado_cuotas_'.$año.'_'.preg_replace('/\W+/', '_', (string) $hermano->numero_hermano).'.pdf';

        return $pdf->stream($slug);
    }
}
