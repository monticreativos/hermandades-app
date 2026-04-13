<?php

namespace App\Http\Controllers\Economia;

use App\Http\Controllers\Controller;
use App\Http\Requests\ExportarSepaRequest;
use App\Http\Requests\GenerarCuotasAsientoRequest;
use App\Models\CuentaContable;
use App\Models\Hermano;
use App\Services\Contabilidad\AsientoContableService;
use App\Services\Contabilidad\CuentaAuxiliarContableService;
use App\Services\Contabilidad\CuotaHermanoEstadoService;
use App\Services\Contabilidad\SepaPain008Generator;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EconomiaCuotasController extends Controller
{
    public function __construct(
        private readonly AsientoContableService $asientoContableService,
        private readonly SepaPain008Generator $sepaPain008Generator,
        private readonly CuotaHermanoEstadoService $cuotaHermanoEstadoService,
        private readonly CuentaAuxiliarContableService $cuentaAuxiliarContableService
    ) {}

    public function index(): View
    {
        $cuentaHaber = CuentaContable::query()->where('codigo', '752')->first()
            ?? CuentaContable::query()->where('codigo', '700')->first();

        $cuentas = CuentaContable::query()->orderBy('codigo')->get(['id', 'codigo', 'nombre']);

        return view('economia.cuotas.index', [
            'cuentaHaberDefault' => $cuentaHaber,
            'cuentas' => $cuentas,
        ]);
    }

    public function generarAsiento(GenerarCuotasAsientoRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $fecha = Carbon::now();

        try {
            $ejercicio = $this->asientoContableService->ejercicioParaFecha($fecha);
        } catch (\RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        $query = Hermano::query()->orderBy('numero_hermano');
        if ($data['grupo'] === 'todos_alta') {
            $query->where('estado', 'Alta');
        }

        $hermanos = $query->get();
        if ($hermanos->isEmpty()) {
            return back()->withInput()->with('error', 'No hay hermanos que cumplan el criterio seleccionado.');
        }

        $importe = round((float) $data['importe'], 2);
        $lineas = [];

        foreach ($hermanos as $hermano) {
            $cuentaDeudorHermano = $this->cuentaAuxiliarContableService->obtenerOCrearParaHermano($hermano);
            $lineas[] = [
                'cuenta_contable_id' => $cuentaDeudorHermano->id,
                'debe' => $importe,
                'haber' => 0,
                'concepto_detalle' => 'Cuota — n.º '.$hermano->numero_hermano.' '.$hermano->apellidos.', '.$hermano->nombre,
            ];
        }

        $totalDebe = array_sum(array_column($lineas, 'debe'));
        $lineas[] = [
            'cuenta_contable_id' => (int) $data['cuenta_haber_id'],
            'debe' => 0,
            'haber' => round($totalDebe, 2),
            'concepto_detalle' => 'Cuotas al cobro ('.$hermanos->count().' hermanos)',
        ];

        DB::transaction(function () use ($ejercicio, $fecha, $data, $lineas, $hermanos): void {
            $this->asientoContableService->crearAsiento(
                $ejercicio,
                $fecha->format('Y-m-d'),
                $data['glosa'],
                $lineas
            );

            $this->cuotaHermanoEstadoService->marcarPendientesTrasEmisionMasiva($hermanos, (int) $ejercicio->id);
        });

        return redirect()
            ->route('economia.libro-diario.index')
            ->with('status', 'Asiento de cuotas generado: '.$hermanos->count().' líneas al debe y contrapartida en ingresos. Los hermanos en alta quedan marcados con cuota pendiente hasta el cobro en bancos (572).');
    }

    public function exportarSepa(ExportarSepaRequest $request): StreamedResponse|RedirectResponse
    {
        $data = $request->validated();

        $query = Hermano::query()
            ->whereNotNull('iban')
            ->where('iban', '!=', '')
            ->orderBy('numero_hermano');

        if ($data['grupo'] === 'todos_alta') {
            $query->where('estado', 'Alta');
        }

        $hermanos = $query->get()->filter(function (Hermano $h): bool {
            $iban = preg_replace('/\s+/', '', (string) $h->iban);

            return strlen($iban) >= 15;
        })->values();

        if ($hermanos->isEmpty()) {
            return back()->withInput()->with('error', 'No hay hermanos con IBAN válido para la remesa.');
        }

        $xml = $this->sepaPain008Generator->generar(
            $hermanos,
            number_format((float) $data['importe'], 2, '.', ''),
            $data['concepto'],
            isset($data['fecha_cobro']) ? Carbon::parse($data['fecha_cobro'])->format('Y-m-d') : null
        );

        $nombre = 'sepa_cuotas_'.now()->format('Ymd_His').'.xml';

        return response()->streamDownload(
            static function () use ($xml): void {
                echo $xml;
            },
            $nombre,
            [
                'Content-Type' => 'application/xml; charset=UTF-8',
            ]
        );
    }
}
