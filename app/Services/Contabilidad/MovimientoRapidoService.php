<?php

namespace App\Services\Contabilidad;

use App\Models\Asiento;
use App\Models\CuentaContable;
use App\Models\DocumentoGasto;
use App\Models\Hermano;
use App\Models\Proveedor;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class MovimientoRapidoService
{
    public function __construct(
        private readonly AsientoContableService $asientoContableService,
        private readonly CuotaHermanoEstadoService $cuotaHermanoEstadoService,
        private readonly CuentaAuxiliarContableService $cuentaAuxiliarContableService
    ) {}

    /**
     * Cobro de cuota ordinaria desde el portal (Bizum simulado → bancos).
     *
     * @throws \RuntimeException
     */
    public function registrarCobroCuotaPortal(Hermano $hermano, float $importe, string $canal = 'portal_bizum'): Asiento
    {
        if (! $hermano->tieneCuotaOrdinariaPendiente()) {
            throw new \RuntimeException('No consta cuota pendiente para este hermano.');
        }

        $importe = round($importe, 2);
        if ($importe <= 0) {
            throw new \RuntimeException('El importe debe ser mayor que cero.');
        }

        $fecha = Carbon::now();
        $ejercicio = $this->asientoContableService->ejercicioParaFecha($fecha);

        $cuentaTesoreria = $this->cuentaPorCodigoPref('572');
        $cuentaDeudor = $this->cuentaAuxiliarContableService->obtenerOCrearParaHermano($hermano);

        $lineas = [
            [
                'cuenta_contable_id' => $cuentaTesoreria->id,
                'debe' => $importe,
                'haber' => 0,
                'concepto_detalle' => 'Cobro cuota (Bizum portal) — n.º '.$hermano->numero_hermano,
            ],
            [
                'cuenta_contable_id' => $cuentaDeudor->id,
                'debe' => 0,
                'haber' => $importe,
                'concepto_detalle' => 'Cobro cuota — n.º '.$hermano->numero_hermano.' '.$hermano->apellidos.', '.$hermano->nombre,
            ],
        ];

        $glosa = 'Cobro cuota hermano n.º '.$hermano->numero_hermano.' (portal)';

        $asiento = null;
        DB::transaction(function () use ($ejercicio, $fecha, $glosa, $lineas, $hermano, $canal, &$asiento): void {
            $asiento = $this->asientoContableService->crearAsiento(
                $ejercicio,
                $fecha->format('Y-m-d'),
                $glosa,
                $lineas,
                [
                    'movimiento_rapido' => true,
                    'canal_origen' => $canal,
                    'categoria_economia' => CategoriaMovimientoEconomia::IngresoCuota->value,
                    'hermano_id' => $hermano->id,
                    'apt_modelo_182' => false,
                    'operacion_exenta_iva' => true,
                    'renta_is_exenta' => true,
                    'base_imponible' => null,
                    'cuota_iva' => null,
                ]
            );
        });

        $asiento = $asiento->fresh(['apuntes.cuentaContable']);
        $this->cuotaHermanoEstadoService->aplicarCobroCuotasSiProcede($asiento);

        return $asiento;
    }

    /**
     * @param  array{
     *     categoria: CategoriaMovimientoEconomia,
     *     fecha: Carbon,
     *     importe: float,
     *     metodo_tesoreria: 'caja'|'banco',
     *     hermano_id?: int|null,
     *     apt_modelo_182?: bool,
     *     glosa_personalizada?: string|null,
     *     proveedor_texto?: string|null,
     *     base_imponible?: float|null,
     *     cuota_iva?: float|null,
     *     archivo?: UploadedFile|null,
     *     canal_origen?: string,
     *     proveedor_id?: int|null
     * }  $datos
     */
    public function registrar(array $datos): Asiento
    {
        $categoria = $datos['categoria'];
        $fecha = $datos['fecha'];
        $metodo = $datos['metodo_tesoreria'];
        $canal = $datos['canal_origen'] ?? 'manual_rapido';

        $baseIva = isset($datos['base_imponible']) ? round((float) $datos['base_imponible'], 2) : null;
        $cuotaIva = isset($datos['cuota_iva']) ? round((float) $datos['cuota_iva'], 2) : null;
        $conIva = $baseIva !== null && $cuotaIva !== null && $baseIva > 0 && $cuotaIva > 0.004;

        $importe = round((float) $datos['importe'], 2);
        if (! $categoria->esIngreso() && $conIva) {
            $importe = round((float) $baseIva + (float) $cuotaIva, 2);
        }

        if ($importe <= 0) {
            throw new \InvalidArgumentException('El importe debe ser mayor que cero.');
        }

        $ejercicio = $this->asientoContableService->ejercicioParaFecha($fecha);

        $cuentaTesoreria = $metodo === 'caja'
            ? $this->cuentaPorCodigoPref('570')
            : $this->cuentaPorCodigoPref('572');

        $hermano = isset($datos['hermano_id']) && $datos['hermano_id']
            ? Hermano::query()->findOrFail((int) $datos['hermano_id'])
            : null;

        $proveedor = isset($datos['proveedor_id']) && $datos['proveedor_id']
            ? Proveedor::query()->findOrFail((int) $datos['proveedor_id'])
            : null;

        $apt182 = (bool) ($datos['apt_modelo_182'] ?? false);
        if ($apt182 && $categoria !== CategoriaMovimientoEconomia::IngresoDonativo) {
            $apt182 = false;
        }

        if ($categoria === CategoriaMovimientoEconomia::IngresoCuota && ! $hermano) {
            throw new \InvalidArgumentException('Debe seleccionar el hermano para registrar el cobro de cuota.');
        }

        if ($apt182) {
            if (! $hermano) {
                throw new \InvalidArgumentException('Para donativos con desgravación fiscal debe indicar el donante (hermano).');
            }
            $this->validarDatosFiscalesDonante($hermano);
        }

        $lineas = [];
        $glosa = $datos['glosa_personalizada'] ?? $this->glosaPorDefecto($categoria, $hermano, $importe, $proveedor);

        if ($categoria === CategoriaMovimientoEconomia::PagoProveedor) {
            if (! $proveedor) {
                throw new \InvalidArgumentException('Indique el proveedor cuya deuda liquida.');
            }
            if ($conIva) {
                throw new \InvalidArgumentException('En «Pago a proveedor» registre el importe total liquidado sin desglose de IVA.');
            }
            $lineas = $this->lineasPagoProveedor($cuentaTesoreria, $importe, $proveedor);
        } elseif ($categoria->esIngreso()) {
            $lineas = $this->lineasIngreso(
                $categoria,
                $cuentaTesoreria,
                $importe,
                $hermano,
                $conIva,
                $baseIva,
                $cuotaIva
            );
        } else {
            $lineas = $this->lineasGasto(
                $categoria,
                $cuentaTesoreria,
                $importe,
                $conIva,
                $baseIva ?? 0,
                $cuotaIva ?? 0,
                $proveedor
            );
        }

        $operacionExentaIva = match (true) {
            $categoria === CategoriaMovimientoEconomia::IngresoActividadEconomica => false,
            $categoria->esIngreso() => true,
            $categoria === CategoriaMovimientoEconomia::PagoProveedor => true,
            default => ! $conIva,
        };

        $atributos = [
            'movimiento_rapido' => true,
            'canal_origen' => $canal,
            'categoria_economia' => $categoria->value,
            'hermano_id' => $hermano?->id,
            'apt_modelo_182' => $apt182,
            'operacion_exenta_iva' => $operacionExentaIva,
            'renta_is_exenta' => $categoria->rentaIsExentaPorDefecto(),
            'base_imponible' => $conIva ? $baseIva : null,
            'cuota_iva' => $conIva ? $cuotaIva : null,
        ];

        $asiento = null;
        DB::transaction(function () use ($ejercicio, $fecha, $glosa, $lineas, $atributos, &$asiento): void {
            $asiento = $this->asientoContableService->crearAsiento(
                $ejercicio,
                $fecha->format('Y-m-d'),
                $glosa,
                $lineas,
                $atributos
            );
        });

        $asiento = $asiento->fresh(['apuntes']);

        if (! $categoria->esIngreso() && ($datos['archivo'] ?? null) instanceof UploadedFile) {
            $this->adjuntarDocumentoGasto(
                $asiento,
                $datos['archivo'],
                $datos['proveedor_texto'] ?? null,
                $conIva ? (float) $baseIva + (float) $cuotaIva : $importe,
                $proveedor?->id
            );
        }

        if ($categoria === CategoriaMovimientoEconomia::IngresoCuota) {
            $this->cuotaHermanoEstadoService->aplicarCobroCuotasSiProcede($asiento->load('apuntes.cuentaContable'));
        }

        return $asiento->fresh(['apuntes.cuentaContable', 'documentosGasto']);
    }

    private function glosaPorDefecto(CategoriaMovimientoEconomia $cat, ?Hermano $h, float $importe, ?Proveedor $proveedor = null): string
    {
        $base = $cat->etiqueta().' — '.number_format($importe, 2, ',', '.').' €';
        if ($h && $cat === CategoriaMovimientoEconomia::IngresoCuota) {
            return $base.' (n.º '.$h->numero_hermano.')';
        }
        if ($proveedor && $cat === CategoriaMovimientoEconomia::PagoProveedor) {
            return $base.' — '.$proveedor->razon_social;
        }

        return $base;
    }

    /**
     * Liquidación de deuda con proveedor: Debe subcuenta acreedor / Haber tesorería (PGC).
     *
     * @return list<array{cuenta_contable_id: int, debe: float, haber: float, concepto_detalle?: string|null}>
     */
    private function lineasPagoProveedor(CuentaContable $tesoreria, float $importe, Proveedor $proveedor): array
    {
        $cuentaProveedor = $this->cuentaAuxiliarContableService->obtenerOCrearParaProveedor($proveedor);
        $etiq = $proveedor->razon_social;

        return [
            [
                'cuenta_contable_id' => $cuentaProveedor->id,
                'debe' => $importe,
                'haber' => 0,
                'concepto_detalle' => 'Liquidación deuda — '.$etiq,
            ],
            [
                'cuenta_contable_id' => $tesoreria->id,
                'debe' => 0,
                'haber' => $importe,
                'concepto_detalle' => 'Pago a proveedor (banco/caja)',
            ],
        ];
    }

    /**
     * @return list<array{cuenta_contable_id: int, debe: float, haber: float, concepto_detalle?: string|null}>
     */
    private function lineasIngreso(
        CategoriaMovimientoEconomia $categoria,
        CuentaContable $tesoreria,
        float $importe,
        ?Hermano $hermano,
        bool $conIva,
        ?float $baseIva,
        ?float $cuotaIva
    ): array {
        if ($conIva) {
            throw new \InvalidArgumentException('Los ingresos de cuotas, donativos y lotería se registran sin IVA (operación exenta).');
        }

        if ($categoria === CategoriaMovimientoEconomia::IngresoCuota) {
            $cuentaDeudor = $this->cuentaAuxiliarContableService->obtenerOCrearParaHermano($hermano);

            return [
                [
                    'cuenta_contable_id' => $tesoreria->id,
                    'debe' => $importe,
                    'haber' => 0,
                    'concepto_detalle' => 'Cobro cuota — n.º '.$hermano->numero_hermano,
                ],
                [
                    'cuenta_contable_id' => $cuentaDeudor->id,
                    'debe' => 0,
                    'haber' => $importe,
                    'concepto_detalle' => 'Cobro cuota — n.º '.$hermano->numero_hermano.' '.$hermano->apellidos.', '.$hermano->nombre,
                ],
            ];
        }

        $codigo = $categoria->codigoPgcOperacion();
        $cuentaIngreso = $this->cuentaPorCodigoExactoOPref($codigo);

        return [
            [
                'cuenta_contable_id' => $tesoreria->id,
                'debe' => $importe,
                'haber' => 0,
                'concepto_detalle' => 'Ingreso tesorería — '.$categoria->etiqueta(),
            ],
            [
                'cuenta_contable_id' => $cuentaIngreso->id,
                'debe' => 0,
                'haber' => $importe,
                'concepto_detalle' => $categoria->etiqueta().($hermano ? ' — n.º '.$hermano->numero_hermano : ''),
            ],
        ];
    }

    /**
     * @return list<array{cuenta_contable_id: int, debe: float, haber: float, concepto_detalle?: string|null}>
     */
    private function lineasGasto(
        CategoriaMovimientoEconomia $categoria,
        CuentaContable $tesoreria,
        float $importeTotal,
        bool $conIva,
        float $base,
        float $cuotaIva,
        ?Proveedor $proveedor = null,
    ): array {
        $cuentaGasto = $this->cuentaPorCodigoExactoOPref($categoria->codigoPgcOperacion());
        $etiqProv = $proveedor?->razon_social ?? '';

        if ($proveedor) {
            $cuentaProveedor = $this->cuentaAuxiliarContableService->obtenerOCrearParaProveedor($proveedor);

            if (! $conIva || $cuotaIva <= 0.001) {
                return [
                    [
                        'cuenta_contable_id' => $cuentaGasto->id,
                        'debe' => $importeTotal,
                        'haber' => 0,
                        'concepto_detalle' => $categoria->etiqueta().($etiqProv !== '' ? ' — '.$etiqProv : ''),
                    ],
                    [
                        'cuenta_contable_id' => $cuentaProveedor->id,
                        'debe' => 0,
                        'haber' => $importeTotal,
                        'concepto_detalle' => 'Factura / deuda acreedor — '.$etiqProv,
                    ],
                ];
            }

            $total = round($base + $cuotaIva, 2);
            if (abs($total - $importeTotal) > 0.05) {
                throw new \InvalidArgumentException('La suma de base imponible e IVA debe coincidir con el importe total.');
            }

            $cuenta472 = $this->cuentaPorCodigoPref('472');

            return [
                [
                    'cuenta_contable_id' => $cuentaGasto->id,
                    'debe' => $base,
                    'haber' => 0,
                    'concepto_detalle' => $categoria->etiqueta().' (base imponible)'.($etiqProv !== '' ? ' — '.$etiqProv : ''),
                ],
                [
                    'cuenta_contable_id' => $cuenta472->id,
                    'debe' => $cuotaIva,
                    'haber' => 0,
                    'concepto_detalle' => 'IVA soportado',
                ],
                [
                    'cuenta_contable_id' => $cuentaProveedor->id,
                    'debe' => 0,
                    'haber' => $total,
                    'concepto_detalle' => 'Factura / deuda acreedor — '.$etiqProv,
                ],
            ];
        }

        if (! $conIva || $cuotaIva <= 0.001) {
            return [
                [
                    'cuenta_contable_id' => $cuentaGasto->id,
                    'debe' => $importeTotal,
                    'haber' => 0,
                    'concepto_detalle' => $categoria->etiqueta(),
                ],
                [
                    'cuenta_contable_id' => $tesoreria->id,
                    'debe' => 0,
                    'haber' => $importeTotal,
                    'concepto_detalle' => 'Pago '.$categoria->etiqueta(),
                ],
            ];
        }

        $total = round($base + $cuotaIva, 2);
        if (abs($total - $importeTotal) > 0.05) {
            throw new \InvalidArgumentException('La suma de base imponible e IVA debe coincidir con el importe total.');
        }

        $cuenta472 = $this->cuentaPorCodigoPref('472');

        return [
            [
                'cuenta_contable_id' => $cuentaGasto->id,
                'debe' => $base,
                'haber' => 0,
                'concepto_detalle' => $categoria->etiqueta().' (base imponible)',
            ],
            [
                'cuenta_contable_id' => $cuenta472->id,
                'debe' => $cuotaIva,
                'haber' => 0,
                'concepto_detalle' => 'IVA soportado',
            ],
            [
                'cuenta_contable_id' => $tesoreria->id,
                'debe' => 0,
                'haber' => $total,
                'concepto_detalle' => 'Pago factura (base + IVA)',
            ],
        ];
    }

    private function adjuntarDocumentoGasto(Asiento $asiento, UploadedFile $file, ?string $proveedor, float $importeLinea, ?int $proveedorId = null): void
    {
        if (! $file->isValid()) {
            return;
        }

        $path = $file->store("gastos/{$asiento->id}", 'local');
        DocumentoGasto::query()->create([
            'asiento_id' => $asiento->id,
            'proveedor_id' => $proveedorId,
            'orden_linea' => 0,
            'archivo_path' => $path,
            'nombre_original' => $file->getClientOriginalName(),
            'mime_type' => $file->getClientMimeType(),
            'proveedor' => $proveedor !== null && trim($proveedor) !== '' ? trim($proveedor) : null,
            'estado' => DocumentoGasto::ESTADO_PAGADA,
            'importe_linea' => round($importeLinea, 2),
            'fecha_documento' => $asiento->fecha,
        ]);
    }

    public function validarDatosFiscalesDonante(Hermano $hermano): void
    {
        $errores = [];
        if (blank($hermano->dni)) {
            $errores[] = 'DNI/NIE';
        }
        foreach (['direccion', 'localidad', 'codigo_postal', 'provincia'] as $campo) {
            if (blank($hermano->{$campo})) {
                $errores[] = str_replace('_', ' ', $campo);
            }
        }
        if ($errores !== []) {
            throw new \InvalidArgumentException(
                'Para donativos desgravables el hermano debe tener datos completos en ficha: '.implode(', ', $errores).'. Actualícelos en Secretaría o mediante solicitud de cambio.'
            );
        }
    }

    private function cuentaPorCodigoPref(string $prefijo): CuentaContable
    {
        $c = CuentaContable::query()->where('codigo', 'like', $prefijo.'%')->orderBy('codigo')->first();
        if (! $c) {
            throw new \RuntimeException('No hay ninguna cuenta contable que comience por '.$prefijo.' en el plan. Ejecute el PlanContableSeeder.');
        }

        return $c;
    }

    private function cuentaPorCodigoExactoOPref(string $codigo): CuentaContable
    {
        $exact = CuentaContable::query()->where('codigo', $codigo)->first();
        if ($exact) {
            return $exact;
        }

        return $this->cuentaPorCodigoPref($codigo);
    }
}
