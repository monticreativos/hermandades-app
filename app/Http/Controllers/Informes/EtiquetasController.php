<?php

namespace App\Http\Controllers\Informes;

use App\Http\Controllers\Controller;
use App\Models\ConfiguracionHermandad;
use App\Models\Hermano;
use App\Services\Informes\EtiquetasMailingService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EtiquetasController extends Controller
{
    public function __construct(
        private readonly EtiquetasMailingService $etiquetasMailingService
    ) {}

    public function index(Request $request): View
    {
        $modo = in_array($request->string('modo')->toString(), ['todos', 'cabezas'], true)
            ? $request->string('modo')->toString()
            : 'todos';

        $cp = $request->filled('codigo_postal') ? trim($request->string('codigo_postal')->toString()) : null;

        $etiquetas = $this->etiquetasMailingService->hermanosParaEtiquetas($modo, $cp);

        return view('informes.etiquetas.index', [
            'etiquetas' => $etiquetas,
            'total' => $etiquetas->count(),
            'modo' => $modo,
            'codigoPostal' => $cp ?? '',
        ]);
    }

    public function pdf(Request $request): Response
    {
        $modo = in_array($request->string('modo')->toString(), ['todos', 'cabezas'], true)
            ? $request->string('modo')->toString()
            : 'todos';

        $cp = $request->filled('codigo_postal') ? trim($request->string('codigo_postal')->toString()) : null;

        $lista = $this->etiquetasMailingService->hermanosParaEtiquetas($modo, $cp);
        $hermandad = ConfiguracionHermandad::query()->first();

        $pdf = Pdf::loadView('informes.pdf.etiquetas-a4', [
            'hermandad' => $hermandad,
            'hermanos' => $lista,
            'modo' => $modo,
        ]);

        $pdf->setPaper('A4', 'portrait');

        return $pdf->stream('etiquetas_postales_'.now()->format('Ymd').'.pdf');
    }

    public function csv(Request $request): StreamedResponse
    {
        $modo = in_array($request->string('modo')->toString(), ['todos', 'cabezas'], true)
            ? $request->string('modo')->toString()
            : 'todos';

        $cp = $request->filled('codigo_postal') ? trim($request->string('codigo_postal')->toString()) : null;

        $lista = $this->etiquetasMailingService->hermanosParaEtiquetas($modo, $cp);

        $nombre = 'mailing_hermanos_'.now()->format('Ymd_His').'.csv';

        return response()->streamDownload(function () use ($lista): void {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, [
                'numero_hermano',
                'nombre',
                'apellidos',
                'email',
                'telefono',
                'direccion',
                'codigo_postal',
                'localidad',
                'provincia',
            ], ';');

            foreach ($lista as $h) {
                /** @var Hermano $h */
                fputcsv($out, [
                    $h->numero_hermano,
                    $h->nombre,
                    $h->apellidos,
                    $h->email,
                    $h->telefono,
                    $h->direccion,
                    $h->codigo_postal,
                    $h->localidad,
                    $h->provincia,
                ], ';');
            }

            fclose($out);
        }, $nombre, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
