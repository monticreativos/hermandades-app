<?php

namespace App\Services\Contabilidad;

use App\Models\Aviso;
use App\Models\AvisoHermano;
use App\Models\CuentaContable;
use App\Models\Hermano;
use App\Models\ImportacionRespuestaBanco;
use App\Models\RemesaRecibo;
use App\Models\RemesaSepa;
use App\Models\User;
use App\Notifications\ReciboDevueltoDomiciliacionNotification;
use Carbon\Carbon;
use DOMDocument;
use DOMXPath;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;

class ConciliacionRespuestaBancoService
{
    public function __construct(
        private readonly AsientoContableService $asientoContableService,
        private readonly CuotaPeriodicidadService $periodicidadService,
        private readonly CuotaHermanoEstadoService $cuotaEstadoService,
        private readonly CuentaAuxiliarContableService $cuentaAuxiliarContableService
    ) {}

    /**
     * @return array{importacion: ImportacionRespuestaBanco, detalle: array{cobrados: int, devueltos: int, no_encontrados: int, log: list<array<string, mixed>>}}
     */
    public function procesarArchivo(UploadedFile $archivo, RemesaSepa $remesa, User $user): array
    {
        $ext = strtolower($archivo->getClientOriginalExtension());
        $stored = $archivo->store('importaciones_banco', 'local');
        $contenido = Storage::disk('local')->get($stored);

        if (in_array($ext, ['xml'], true)) {
            $movs = $this->extraerMovimientosCamt053($contenido);
            $tipo = 'camt053';
        } else {
            $movs = $this->extraerMovimientosCsv($contenido);
            $tipo = 'csv';
        }

        $cobrados = 0;
        $devueltos = 0;
        $noEncontrados = 0;
        $log = [];
        /** @var list<RemesaRecibo> $recibosCobrados */
        $recibosCobrados = [];

        DB::beginTransaction();
        try {
            foreach ($movs as $mov) {
                $e2e = $mov['end_to_end_id'];
                $importe = round((float) $mov['importe'], 2);
                $esDev = (bool) ($mov['devuelto'] ?? false);

                $recibo = RemesaRecibo::query()
                    ->where('end_to_end_id', $e2e)
                    ->where('remesa_id', $remesa->id)
                    ->first();

                if (! $recibo) {
                    $recibo = RemesaRecibo::query()->where('end_to_end_id', $e2e)->first();
                }

                if (! $recibo) {
                    $noEncontrados++;
                    $log[] = ['end_to_end_id' => $e2e, 'resultado' => 'no_encontrado'];

                    continue;
                }

                if ($recibo->estado !== RemesaRecibo::ESTADO_PENDIENTE_BANCO) {
                    $log[] = ['end_to_end_id' => $e2e, 'resultado' => 'ya_procesado'];

                    continue;
                }

                if (! $esDev && abs((float) $recibo->importe - $importe) > 0.05) {
                    $noEncontrados++;
                    $log[] = ['end_to_end_id' => $e2e, 'resultado' => 'importe_distinto', 'esperado' => (float) $recibo->importe, 'recibido' => $importe];

                    continue;
                }

                $hermano = $recibo->hermano;
                $ejercicio = $remesa->ejercicio;

                if ($esDev) {
                    $recibo->update([
                        'estado' => RemesaRecibo::ESTADO_DEVUELTO,
                        'fecha_estado' => now(),
                        'motivo_devolucion' => $mov['motivo'] ?? 'Devolución bancaria',
                        'codigo_devolucion' => $mov['codigo'] ?? null,
                    ]);
                    $this->cuotaEstadoService->marcarImpagadaPorDevolucionRemesa($hermano, $ejercicio, $mov['motivo'] ?? null);
                    $this->crearAvisoDevolucion($hermano, $recibo);
                    $devueltos++;
                    $log[] = ['end_to_end_id' => $e2e, 'resultado' => 'devuelto'];
                } else {
                    $recibo->update([
                        'estado' => RemesaRecibo::ESTADO_COBRADO,
                        'fecha_estado' => now(),
                    ]);
                    $this->periodicidadService->marcarPeriodoCobrado($hermano, $recibo->periodo_clave);
                    $this->cuotaEstadoService->refrescarEstadoSegunPeriodicidad($hermano, $ejercicio, $this->periodicidadService);
                    $recibosCobrados[] = $recibo;
                    $cobrados++;
                    $log[] = ['end_to_end_id' => $e2e, 'resultado' => 'cobrado'];
                }
            }

            if ($recibosCobrados !== []) {
                $this->crearAsientoCobroRemesa($remesa, $recibosCobrados);
            }

            $remesa->update([
                'estado' => $recibosCobrados === [] && $cobrados === 0 && $devueltos > 0
                    ? RemesaSepa::ESTADO_CONCILIACION_PARCIAL
                    : ($cobrados > 0 ? RemesaSepa::ESTADO_CONCILIADA : $remesa->estado),
            ]);

            $importacion = ImportacionRespuestaBanco::query()->create([
                'remesa_id' => $remesa->id,
                'user_id' => $user->id,
                'tipo_archivo' => $tipo,
                'archivo_path' => $stored,
                'nombre_original' => $archivo->getClientOriginalName(),
                'resultado_json' => $log,
                'recibos_cobrados' => $cobrados,
                'recibos_devueltos' => $devueltos,
                'recibos_no_encontrados' => $noEncontrados,
            ]);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        return [
            'importacion' => $importacion,
            'detalle' => [
                'cobrados' => $cobrados,
                'devueltos' => $devueltos,
                'no_encontrados' => $noEncontrados,
                'log' => $log,
            ],
        ];
    }

    /**
     * @param  list<RemesaRecibo>  $recibos
     */
    private function crearAsientoCobroRemesa(RemesaSepa $remesa, array $recibos): void
    {
        $cuenta572 = CuentaContable::query()->where('codigo', 'like', '572%')->orderBy('codigo')->first();

        if (! $cuenta572) {
            throw new \RuntimeException('Falta cuenta 572 en el plan contable.');
        }

        $lineasHaber = [];
        $total = 0.0;
        foreach ($recibos as $recibo) {
            $hermano = $recibo->hermano;
            if (! $hermano) {
                throw new \RuntimeException('Hay recibos de remesa sin hermano vinculado; no se puede generar el asiento de cobro.');
            }
            $imp = round((float) $recibo->importe, 2);
            $total += $imp;
            $cuentaDeudor = $this->cuentaAuxiliarContableService->obtenerOCrearParaHermano($hermano);
            $lineasHaber[] = [
                'cuenta_contable_id' => $cuentaDeudor->id,
                'debe' => 0,
                'haber' => $imp,
                'concepto_detalle' => 'Cobro remesa '.$remesa->etiqueta_periodo.' — n.º '.$hermano->numero_hermano.' '.$hermano->apellidos.', '.$hermano->nombre,
            ];
        }

        $lineas = array_merge([[
            'cuenta_contable_id' => $cuenta572->id,
            'debe' => round($total, 2),
            'haber' => 0,
            'concepto_detalle' => 'Cobro domiciliación SEPA — '.$remesa->etiqueta_periodo,
        ]], $lineasHaber);

        $asiento = $this->asientoContableService->crearAsiento(
            $remesa->ejercicio,
            Carbon::today()->format('Y-m-d'),
            'Conciliación remesa SEPA '.$remesa->etiqueta_periodo,
            $lineas,
            [
                'movimiento_rapido' => true,
                'canal_origen' => 'conciliacion_remesa',
                'categoria_economia' => 'ingreso_cuota',
                'operacion_exenta_iva' => true,
                'renta_is_exenta' => true,
            ]
        );

        $remesa->update(['asiento_conciliacion_id' => $asiento->id]);

        foreach ($recibos as $recibo) {
            $recibo->update(['asiento_cobro_id' => $asiento->id]);
        }
    }

    /**
     * @return list<array{end_to_end_id: string, importe: float, devuelto?: bool, motivo?: string|null, codigo?: string|null}>
     */
    private function extraerMovimientosCamt053(string $xml): array
    {
        $dom = new DOMDocument;
        if (@$dom->loadXML($xml) === false) {
            throw new \InvalidArgumentException('XML no válido.');
        }

        $xp = new DOMXPath($dom);
        $resultado = [];
        $endNodes = $xp->query('//*[local-name()="EndToEndId"]');
        if (! $endNodes) {
            return [];
        }

        foreach ($endNodes as $node) {
            $e2e = trim($node->textContent);
            if ($e2e === '' || ! str_starts_with($e2e, 'E2E-H')) {
                continue;
            }

            $ntry = $node->parentNode;
            for ($i = 0; $i < 30 && $ntry !== null; $i++) {
                if ($ntry->localName === 'Ntry') {
                    break;
                }
                $ntry = $ntry->parentNode;
            }
            if (! $ntry || $ntry->localName !== 'Ntry') {
                continue;
            }

            $amtNodes = $xp->query('.//*[local-name()="Amt"]', $ntry);
            $importe = 0.0;
            if ($amtNodes && $amtNodes->length > 0) {
                $importe = abs((float) $amtNodes->item(0)->textContent);
            }

            $rtr = $xp->query('.//*[local-name()="RtrInf"]//*[local-name()="Cd"]', $ntry);
            $esDevolucion = $rtr !== null && $rtr->length > 0;

            $resultado[] = [
                'end_to_end_id' => $e2e,
                'importe' => $importe,
                'devuelto' => $esDevolucion,
                'codigo' => $rtr && $rtr->length > 0 ? trim($rtr->item(0)->textContent) : null,
            ];
        }

        return $resultado;
    }

    /**
     * @return list<array{end_to_end_id: string, importe: float, devuelto: bool, motivo?: string|null}>
     */
    private function extraerMovimientosCsv(string $contenido): array
    {
        $lineas = preg_split('/\r\n|\n|\r/', $contenido) ?: [];
        $out = [];
        foreach ($lineas as $i => $linea) {
            $linea = trim($linea);
            if ($linea === '' || str_starts_with($linea, '#')) {
                continue;
            }
            if ($i === 0 && str_contains(strtolower($linea), 'end_to_end')) {
                continue;
            }
            $partes = str_contains($linea, ';') ? explode(';', $linea) : explode(',', $linea);
            if (count($partes) < 3) {
                continue;
            }
            $e2e = trim($partes[0]);
            $imp = (float) str_replace(',', '.', trim($partes[1]));
            $est = strtoupper(trim($partes[2]));
            $out[] = [
                'end_to_end_id' => $e2e,
                'importe' => $imp,
                'devuelto' => str_contains($est, 'DEV'),
                'motivo' => isset($partes[3]) ? trim($partes[3]) : null,
            ];
        }

        return $out;
    }

    private function crearAvisoDevolucion(Hermano $hermano, RemesaRecibo $recibo): void
    {
        $user = auth()->user();
        $gastos = (float) ($recibo->comision_banco ?? 0);
        if ($gastos <= 0.001) {
            $gastos = (float) config('gestaher.remesa_gastos_devolucion_default_eur', 5);
        }
        $importeRecibo = round((float) $recibo->importe, 2);
        $total = round($importeRecibo + $gastos, 2);
        $urlPagos = url(route('portal.pagos.index', [], false));

        $aviso = Aviso::query()->create([
            'titulo' => 'Devolución de recibo de cuota (domiciliación)',
            'cuerpo' => 'Su recibo SEPA ha sido devuelto por el banco. Periodo: '.$recibo->periodo_clave.'. '.($recibo->motivo_devolucion ? 'Motivo: '.$recibo->motivo_devolucion.'. ' : '')
                .'Importe del recibo: '.number_format($importeRecibo, 2, ',', '.').' €. Gastos de devolución (orientativos): '.number_format($gastos, 2, ',', '.').' €. Total a regularizar: '.number_format($total, 2, ',', '.').' €. '
                .'Puede abonar desde «Pagos» del portal (Bizum): '.$urlPagos.' o en secretaría.',
            'alcance' => Aviso::ALCANCE_INDIVIDUAL,
            'solo_alta' => false,
            'solo_portal' => true,
            'hermano_id' => $hermano->id,
            'creado_por_user_id' => $user?->id,
            'enviado_en' => now(),
        ]);

        AvisoHermano::query()->firstOrCreate(
            ['aviso_id' => $aviso->id, 'hermano_id' => $hermano->id],
            ['leido_en' => null]
        );

        $email = trim((string) $hermano->email);
        if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            try {
                Notification::route('mail', $email)->notify(
                    new ReciboDevueltoDomiciliacionNotification(
                        $hermano,
                        $recibo->fresh(),
                        $importeRecibo,
                        $gastos,
                        $total,
                        $urlPagos
                    )
                );
            } catch (\Throwable) {
                // No interrumpir la conciliación si falla el correo.
            }
        }
    }
}
