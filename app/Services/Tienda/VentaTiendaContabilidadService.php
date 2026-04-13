<?php

namespace App\Services\Tienda;

use App\Models\Asiento;
use App\Models\CuentaContable;
use App\Services\Contabilidad\AsientoContableService;
use Carbon\Carbon;

class VentaTiendaContabilidadService
{
    public function __construct(
        private readonly AsientoContableService $asientoContableService
    ) {}

    /**
     * Debe tesorería (570/572) / Haber 700 (base) + 477 (IVA repercutido) si procede.
     */
    public function crearAsientoVenta(
        Carbon $fecha,
        float $importeTotalTesoreria,
        float $totalBase,
        float $totalIva,
        string $metodoPago,
        ?int $hermanoId,
        string $glosa,
        string $canalOrigen = 'tienda_tpv'
    ): Asiento {
        $importeTotalTesoreria = round($importeTotalTesoreria, 2);
        $totalBase = round($totalBase, 2);
        $totalIva = round($totalIva, 2);

        $ejercicio = $this->asientoContableService->ejercicioParaFecha($fecha);

        $cuentaTesoreria = match ($metodoPago) {
            'efectivo' => $this->cuentaPorPrefijo('570'),
            default => $this->cuentaPorPrefijo('572'),
        };

        $cuenta700 = $this->cuentaPorPrefijo('700');
        $conIva = $totalIva > 0.009;
        $cuenta477 = $conIva ? $this->cuentaPorPrefijo('477') : null;

        if ($conIva && abs($importeTotalTesoreria - round($totalBase + $totalIva, 2)) > 0.05) {
            throw new \RuntimeException('El desglose contable de la venta no cuadra con el total cobrado.');
        }

        if (! $conIva && abs($importeTotalTesoreria - $totalBase) > 0.05) {
            throw new \RuntimeException('El total sin IVA no coincide con la suma de bases.');
        }

        $lineas = [
            [
                'cuenta_contable_id' => $cuentaTesoreria->id,
                'debe' => $importeTotalTesoreria,
                'haber' => 0,
                'concepto_detalle' => 'Cobro tienda — '.$metodoPago,
            ],
            [
                'cuenta_contable_id' => $cuenta700->id,
                'debe' => 0,
                'haber' => $totalBase,
                'concepto_detalle' => 'Ventas de mercaderías (tienda)',
            ],
        ];

        if ($conIva && $cuenta477) {
            $lineas[] = [
                'cuenta_contable_id' => $cuenta477->id,
                'debe' => 0,
                'haber' => $totalIva,
                'concepto_detalle' => 'IVA repercutido',
            ];
        }

        return $this->asientoContableService->crearAsiento(
            $ejercicio,
            $fecha->format('Y-m-d'),
            $glosa,
            $lineas,
            [
                'movimiento_rapido' => true,
                'canal_origen' => $canalOrigen,
                'categoria_economia' => 'venta_tienda',
                'hermano_id' => $hermanoId,
                'apt_modelo_182' => false,
                'operacion_exenta_iva' => ! $conIva,
                'renta_is_exenta' => false,
                'base_imponible' => $conIva ? $totalBase : null,
                'cuota_iva' => $conIva ? $totalIva : null,
            ]
        );
    }

    private function cuentaPorPrefijo(string $prefijo): CuentaContable
    {
        $c = CuentaContable::query()
            ->where('codigo', 'like', $prefijo.'%')
            ->orderBy('codigo')
            ->first();

        if (! $c) {
            throw new \RuntimeException('No existe cuenta contable con prefijo '.$prefijo.' (plan contable).');
        }

        return $c;
    }
}
