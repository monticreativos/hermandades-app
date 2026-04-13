<?php

namespace App\Http\Controllers\Informes;

use App\Http\Controllers\Controller;
use App\Models\Actividad;
use App\Models\ConfiguracionHermandad;
use App\Services\Informes\CensoElectoralService;
use App\Support\RegistroActividad;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class CensoElectoralController extends Controller
{
    public function __construct(
        private readonly CensoElectoralService $censoElectoralService
    ) {}

    public function index(Request $request): View
    {
        $config = ConfiguracionHermandad::query()->first();
        $defAnt = (int) ($config?->censo_antiguedad_anos ?? 1);
        $defAnt = max(0, min(80, $defAnt));

        $fechaInforme = $request->filled('fecha_informe')
            ? Carbon::parse($request->string('fecha_informe')->toString())->startOfDay()
            : Carbon::now()->startOfDay();

        $antiguedadAnos = $request->filled('antiguedad_anos')
            ? max(0, min(80, $request->integer('antiguedad_anos')))
            : $defAnt;

        $excluirMorosos = $request->boolean('excluir_morosos', false);

        $query = $this->censoElectoralService->queryVotantes($fechaInforme, $antiguedadAnos, $excluirMorosos);
        $total = (clone $query)->count();
        $votantes = (clone $query)->paginate(40)->withQueryString();

        return view('informes.censo.index', [
            'votantes' => $votantes,
            'total' => $total,
            'fechaInforme' => $fechaInforme,
            'antiguedadAnos' => $antiguedadAnos,
            'excluirMorosos' => $excluirMorosos,
            'defAntiguedadHermandad' => $defAnt,
        ]);
    }

    public function pdf(Request $request): Response
    {
        $config = ConfiguracionHermandad::query()->first();
        $defAnt = max(0, min(80, (int) ($config?->censo_antiguedad_anos ?? 1)));

        $fechaInforme = $request->filled('fecha_informe')
            ? Carbon::parse($request->string('fecha_informe')->toString())->startOfDay()
            : Carbon::now()->startOfDay();

        $antiguedadAnos = $request->filled('antiguedad_anos')
            ? max(0, min(80, $request->integer('antiguedad_anos')))
            : $defAnt;

        $excluirMorosos = $request->boolean('excluir_morosos', false);

        $votantes = $this->censoElectoralService
            ->queryVotantes($fechaInforme, $antiguedadAnos, $excluirMorosos)
            ->get();

        RegistroActividad::registrar(
            Actividad::ACCION_CENSO_PDF,
            'Generación censo electoral PDF ('.$votantes->count().' votantes). Fecha informe '.$fechaInforme->format('d/m/Y').', antigüedad mín. '.$antiguedadAnos.' año(s).'
                .($excluirMorosos ? ' Excluye morosos (lotería y cuota ordinaria pendiente).' : '')
        );

        $pdf = Pdf::loadView('informes.pdf.censo-electoral', [
            'hermandad' => $config,
            'votantes' => $votantes,
            'fechaInforme' => $fechaInforme,
            'antiguedadAnos' => $antiguedadAnos,
            'excluirMorosos' => $excluirMorosos,
            'total' => $votantes->count(),
            'enmascararDni' => fn (?string $dni) => $this->censoElectoralService->enmascararDni($dni),
        ]);

        $pdf->setPaper('A4', 'portrait');

        $nombre = 'censo_electoral_'.$fechaInforme->format('Ymd').'.pdf';

        return $pdf->stream($nombre);
    }
}
