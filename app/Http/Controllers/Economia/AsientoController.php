<?php

namespace App\Http\Controllers\Economia;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAsientoRequest;
use App\Http\Requests\UpdateAsientoRequest;
use App\Models\Actividad;
use App\Models\Asiento;
use App\Services\Contabilidad\AsientoContableService;
use App\Services\Contabilidad\CuotaHermanoEstadoService;
use App\Services\Tesoreria\DocumentoGastoSyncService;
use App\Support\RegistroActividad;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class AsientoController extends Controller
{
    public function __construct(
        private readonly AsientoContableService $asientoContableService,
        private readonly DocumentoGastoSyncService $documentoGastoSyncService,
        private readonly CuotaHermanoEstadoService $cuotaHermanoEstadoService
    ) {}

    public function store(StoreAsientoRequest $request): RedirectResponse
    {
        $fecha = Carbon::parse($request->validated('fecha'));

        try {
            $ejercicio = $this->asientoContableService->ejercicioParaFecha($fecha);
        } catch (\RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        $lineas = $this->normalizarLineas($request->input('apuntes', []));

        try {
            $asiento = null;
            DB::transaction(function () use ($ejercicio, $fecha, $request, $lineas, &$asiento): void {
                $asiento = $this->asientoContableService->crearAsiento(
                    $ejercicio,
                    $fecha->format('Y-m-d'),
                    $request->validated('glosa'),
                    $lineas
                );
                $this->documentoGastoSyncService->sincronizar($asiento, $request, $lineas);
            });
            if ($asiento) {
                $asiento->refresh()->load('apuntes.cuentaContable');
                $this->cuotaHermanoEstadoService->aplicarCobroCuotasSiProcede($asiento);
            }
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', 'No se pudo registrar el asiento.');
        }

        return redirect()->route('economia.libro-diario.index')->with('status', 'Asiento registrado correctamente.');
    }

    public function update(UpdateAsientoRequest $request, Asiento $asiento): RedirectResponse
    {
        if (! $asiento->ejercicio->estaAbierto()) {
            return back()->withInput()->with('error', 'No se puede editar un asiento de un ejercicio cerrado.');
        }

        $fecha = Carbon::parse($request->validated('fecha'));

        try {
            $ejercicioNuevo = $this->asientoContableService->ejercicioParaFecha($fecha);
        } catch (\RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        if ($ejercicioNuevo->id !== $asiento->ejercicio_id) {
            return back()->withInput()->with('error', 'No se puede cambiar el asiento de ejercicio contable. Elimine y vuelva a crearlo.');
        }

        $lineas = $this->normalizarLineas($request->input('apuntes', []));

        try {
            DB::transaction(function () use ($asiento, $fecha, $request, $lineas): void {
                $asiento->load('apuntes.cuentaContable');
                $this->cuotaHermanoEstadoService->revertirCobroCuotasSiProcede($asiento);

                $this->asientoContableService->actualizarAsiento(
                    $asiento,
                    $fecha->format('Y-m-d'),
                    $request->validated('glosa'),
                    $lineas
                );
                $asiento->refresh();
                $this->documentoGastoSyncService->sincronizar($asiento, $request, $lineas);
                $asiento->refresh()->load('apuntes.cuentaContable');
                $this->cuotaHermanoEstadoService->aplicarCobroCuotasSiProcede($asiento);
            });
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', 'No se pudo actualizar el asiento.');
        }

        return redirect()->route('economia.libro-diario.index')->with('status', 'Asiento actualizado.');
    }

    public function destroy(Asiento $asiento): RedirectResponse
    {
        if (! $asiento->ejercicio->estaAbierto()) {
            return back()->with('error', 'No se puede eliminar un asiento de un ejercicio cerrado.');
        }

        DB::transaction(function () use ($asiento): void {
            $asiento->load('apuntes.cuentaContable');
            $this->cuotaHermanoEstadoService->revertirCobroCuotasSiProcede($asiento);

            RegistroActividad::registrar(
                Actividad::ACCION_ELIMINAR_ASIENTO,
                'Eliminado asiento n.º '.$asiento->numero_asiento.' del ejercicio '.$asiento->ejercicio->año.': '.$asiento->glosa
            );

            $this->asientoContableService->eliminarAsiento($asiento);
        });

        return redirect()->route('economia.libro-diario.index')->with('status', 'Asiento eliminado.');
    }

    /**
     * @param  array<int, mixed>  $apuntes
     * @return array<int, array{cuenta_contable_id: int, debe: float, haber: float, concepto_detalle: ?string}>
     */
    private function normalizarLineas(array $apuntes): array
    {
        $lineas = [];

        foreach ($apuntes as $row) {
            if (! is_array($row)) {
                continue;
            }
            $lineas[] = [
                'cuenta_contable_id' => (int) ($row['cuenta_contable_id'] ?? 0),
                'debe' => (float) ($row['debe'] ?? 0),
                'haber' => (float) ($row['haber'] ?? 0),
                'concepto_detalle' => isset($row['concepto_detalle']) ? (string) $row['concepto_detalle'] : null,
            ];
        }

        return $lineas;
    }
}
