<?php

namespace App\Services\Contabilidad;

/**
 * Categorías del asistente «Registrar movimiento» (sin códigos de cuenta visibles).
 * Mapeo PGC interno en {@see MovimientoRapidoService}.
 */
enum CategoriaMovimientoEconomia: string
{
    case IngresoCuota = 'ingreso_cuota';
    case IngresoDonativo = 'ingreso_donativo';
    case IngresoLoteria = 'ingreso_loteria';
    case IngresoActividadEconomica = 'ingreso_actividad_economica';
    case GastoFlores = 'gasto_flores';
    case GastoCera = 'gasto_cera';
    case GastoCultos = 'gasto_cultos';
    case GastoCaridad = 'gasto_caridad';
    /** Liquidación de factura: Debe subcuenta proveedor (410/400) / Haber banco o caja. */
    case PagoProveedor = 'pago_proveedor';

    public function etiqueta(): string
    {
        return match ($this) {
            self::IngresoCuota => 'Cuota de hermano (cobro)',
            self::IngresoDonativo => 'Donativo',
            self::IngresoLoteria => 'Lotería (ingreso)',
            self::IngresoActividadEconomica => 'Bar, recuerdos u otra actividad económica',
            self::GastoFlores => 'Compra de flores',
            self::GastoCera => 'Cera y velas',
            self::GastoCultos => 'Cultos (misas, cultos, honores)',
            self::GastoCaridad => 'Caridad y ayudas monetarias',
            self::PagoProveedor => 'Pago a proveedor (liquidar deuda en su subcuenta)',
        };
    }

    public function esIngreso(): bool
    {
        return str_starts_with($this->value, 'ingreso_');
    }

    /** Código PGC de la cuenta de ingreso o gasto (sin tesorería). */
    public function codigoPgcOperacion(): string
    {
        return match ($this) {
            self::IngresoCuota => '431',
            self::IngresoDonativo => '722',
            self::IngresoLoteria => '700',
            self::IngresoActividadEconomica => '700',
            self::GastoFlores => '600',
            self::GastoCera => '628',
            self::GastoCultos => '623',
            self::GastoCaridad => '650',
            self::PagoProveedor => '410',
        };
    }

    public function rentaIsExentaPorDefecto(): bool
    {
        return match ($this) {
            self::IngresoActividadEconomica => false,
            default => true,
        };
    }
}
