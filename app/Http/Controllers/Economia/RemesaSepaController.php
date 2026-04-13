<?php

namespace App\Http\Controllers\Economia;

use App\Http\Controllers\Controller;
use App\Http\Requests\Economia\ImportarRespuestaRemesaRequest;
use App\Http\Requests\Economia\StoreRemesaSepaRequest;
use App\Models\RemesaRecibo;
use App\Models\RemesaSepa;
use App\Services\Contabilidad\AsientoContableService;
use App\Services\Contabilidad\ConciliacionRespuestaBancoService;
use App\Services\Contabilidad\RemesaSepaGeneracionService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class RemesaSepaController extends Controller
{
    public function __construct(
        private readonly RemesaSepaGeneracionService $generacionService,
        private readonly ConciliacionRespuestaBancoService $conciliacionService,
        private readonly AsientoContableService $asientoContableService,
    ) {}

    public function index(): View
    {
        $remesas = RemesaSepa::query()
            ->with('ejercicio')
            ->orderByDesc('fecha_emision')
            ->orderByDesc('id')
            ->paginate(15);

        $statsGlobales = [
            'pendientes' => RemesaRecibo::query()->where('estado', RemesaRecibo::ESTADO_PENDIENTE_BANCO)->count(),
            'cobrados' => RemesaRecibo::query()->where('estado', RemesaRecibo::ESTADO_COBRADO)->count(),
            'devueltos' => RemesaRecibo::query()->where('estado', RemesaRecibo::ESTADO_DEVUELTO)->count(),
        ];

        return view('economia.remesas.index', compact('remesas', 'statsGlobales'));
    }

    public function create(): View
    {
        return view('economia.remesas.create');
    }

    public function store(StoreRemesaSepaRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $fechaRef = Carbon::createFromDate((int) $data['año'], (int) $data['mes'], 1)->startOfDay();

        try {
            $ejercicio = $this->asientoContableService->ejercicioParaFecha($fechaRef);
        } catch (\RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        try {
            $resultado = $this->generacionService->generarRemesa(
                $ejercicio,
                (int) $data['año'],
                (int) $data['mes'],
                $data['fecha_cobro'],
                $request->user(),
            );
        } catch (\RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('economia.remesas.show', $resultado['remesa'])
            ->with('status', 'Remesa generada. Descargue el XML pain.008 y súbalo a su banco. Cuando reciba la respuesta (camt.053 o CSV auxiliar), impórtela en esta misma pantalla para conciliar y registrar el asiento de cobro.');
    }

    public function show(RemesaSepa $remesa): View
    {
        $remesa->load(['ejercicio', 'recibos.hermano', 'usuario', 'importacionesRespuesta.usuario']);

        $recibos = $remesa->recibos;
        $n = $recibos->count();
        $cob = $recibos->where('estado', RemesaRecibo::ESTADO_COBRADO)->count();
        $dev = $recibos->where('estado', RemesaRecibo::ESTADO_DEVUELTO)->count();
        $pend = $recibos->where('estado', RemesaRecibo::ESTADO_PENDIENTE_BANCO)->count();

        $donut = $n > 0 ? [
            'cob' => round(100 * $cob / $n, 1),
            'dev' => round(100 * $dev / $n, 1),
            'pend' => round(100 * $pend / $n, 1),
        ] : ['cob' => 0.0, 'dev' => 0.0, 'pend' => 0.0];

        $devueltos = $recibos->where('estado', RemesaRecibo::ESTADO_DEVUELTO)->sortBy('hermano.numero_hermano')->values();

        $importaciones = $remesa->importacionesRespuesta->sortByDesc('created_at')->take(8)->values();

        return view('economia.remesas.show', compact(
            'remesa',
            'donut',
            'cob',
            'dev',
            'pend',
            'devueltos',
            'importaciones',
            'recibos',
        ));
    }

    public function descargarXml(RemesaSepa $remesa): StreamedResponse|RedirectResponse
    {
        $path = $remesa->archivo_xml_path;
        if (! $path || ! Storage::disk('local')->exists($path)) {
            return redirect()
                ->route('economia.remesas.show', $remesa)
                ->with('error', 'No se encuentra el archivo XML de esta remesa.');
        }

        return Storage::disk('local')->download($path, 'pain008_remesa_'.$remesa->id.'.xml');
    }

    public function importarRespuesta(ImportarRespuestaRemesaRequest $request, RemesaSepa $remesa): RedirectResponse
    {
        $user = $request->user();
        abort_if(! $user, 403);

        try {
            $detalle = $this->conciliacionService->procesarArchivo(
                $request->file('archivo_respuesta'),
                $remesa,
                $user,
            )['detalle'];
        } catch (\Throwable $e) {
            return redirect()
                ->route('economia.remesas.show', $remesa)
                ->with('error', 'No se pudo procesar el archivo: '.$e->getMessage());
        }

        return redirect()
            ->route('economia.remesas.show', $remesa)
            ->with('status', sprintf(
                'Importación completada: %d cobrados, %d devueltos, %d sin coincidencia en esta remesa.',
                $detalle['cobrados'],
                $detalle['devueltos'],
                $detalle['no_encontrados'],
            ));
    }

    public function devoluciones(): View
    {
        $recibos = RemesaRecibo::query()
            ->where('estado', RemesaRecibo::ESTADO_DEVUELTO)
            ->with(['hermano', 'remesa.ejercicio'])
            ->orderByDesc('fecha_estado')
            ->orderByDesc('id')
            ->paginate(20);

        return view('economia.remesas.devoluciones', compact('recibos'));
    }
}
