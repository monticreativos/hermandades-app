<?php

namespace App\Http\Controllers\Economia;

use App\Http\Controllers\Controller;
use App\Models\ConfiguracionHermandad;
use App\Models\InformeHistorial;
use App\Services\Contabilidad\ArqueoTesoreriaMensualService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ArqueoTesoreriaController extends Controller
{
    public function __construct(
        private readonly ArqueoTesoreriaMensualService $arqueoService
    ) {}

    public function create(Request $request): View
    {
        $ref = now()->subMonth();
        $año = (int) $request->integer('año', $ref->year);
        $mes = (int) $request->integer('mes', $ref->month);
        $mes = max(1, min(12, $mes));
        $año = max(2000, min((int) now()->year + 1, $año));

        $resumen = $this->arqueoService->resumenMes($año, $mes);
        $hermandad = ConfiguracionHermandad::query()->first();

        return view('economia.tesoreria.arqueo-mensual', [
            'resumen' => $resumen,
            'año' => $año,
            'mes' => $mes,
            'hermandad' => $hermandad,
        ]);
    }

    public function pdf(Request $request): RedirectResponse|StreamedResponse
    {
        $data = $request->validate([
            'año' => ['required', 'integer', 'min:2000', 'max:2100'],
            'mes' => ['required', 'integer', 'min:1', 'max:12'],
            'guardar_historial' => ['sometimes', 'boolean'],
        ]);

        $año = (int) $data['año'];
        $mes = (int) $data['mes'];
        $resumen = $this->arqueoService->resumenMes($año, $mes);
        $hermandad = ConfiguracionHermandad::query()->firstOrFail();

        $pdf = Pdf::loadView('informes.pdf.arqueo-tesoreria-mensual', [
            'hermandad' => $hermandad,
            'etiquetaMes' => $resumen['etiqueta_mes'],
            'mesInicio' => $resumen['mes_inicio'],
            'mesFin' => $resumen['mes_fin'],
            'cuentas' => $resumen['cuentas'],
            'totales' => $resumen['totales'],
        ]);
        $pdf->setPaper('A4', 'portrait');
        $nombre = 'arqueo_tesoreria_'.$año.'_'.str_pad((string) $mes, 2, '0', STR_PAD_LEFT).'.pdf';

        if ($request->boolean('guardar_historial')) {
            $bin = $pdf->output();
            $path = 'informes_historial/'.$año.'/'.str_pad((string) $mes, 2, '0', STR_PAD_LEFT).'/'.$nombre;
            Storage::disk('local')->put($path, $bin);

            InformeHistorial::query()->create([
                'tipo' => InformeHistorial::TIPO_ARQUEO_TESORERIA_MENSUAL,
                'titulo' => 'Arqueo tesorería '.$resumen['etiqueta_mes'],
                'periodo_año' => $año,
                'periodo_mes' => $mes,
                'archivo_path' => $path,
                'user_id' => $request->user()?->id,
                'metadata' => [
                    'totales' => $resumen['totales'],
                ],
            ]);

            return redirect()
                ->route('economia.informes.historial')
                ->with('status', 'PDF de arqueo generado y guardado en el historial de informes.');
        }

        return $pdf->stream($nombre);
    }
}
