<?php

namespace App\Services\Contabilidad;

use App\Models\Apunte;
use App\Models\CuentaContable;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ArqueoTesoreriaMensualService
{
    /**
     * Cuentas 570 y 572 (caja y bancos).
     *
     * @return Collection<int, CuentaContable>
     */
    public function cuentasTesoreria(): Collection
    {
        return CuentaContable::query()
            ->where(function ($q): void {
                $q->where('codigo', 'like', '570%')
                    ->orWhere('codigo', 'like', '572%');
            })
            ->orderBy('codigo')
            ->get();
    }

    /**
     * @return array{
     *   mes_inicio: string,
     *   mes_fin: string,
     *   etiqueta_mes: string,
     *   cuentas: list<array{cuenta: CuentaContable, saldo_inicial: float, ingresos: float, gastos: float, saldo_final: float}>,
     *   totales: array{saldo_inicial: float, ingresos: float, gastos: float, saldo_final: float}
     * }
     */
    public function resumenMes(int $año, int $mes): array
    {
        $inicio = Carbon::createFromDate($año, $mes, 1)->startOfDay();
        $fin = (clone $inicio)->endOfMonth();

        $cuentas = $this->cuentasTesoreria();
        $filas = [];
        $totIni = 0.0;
        $totIng = 0.0;
        $totGas = 0.0;
        $totFin = 0.0;

        foreach ($cuentas as $c) {
            $saldoIni = $this->saldoCuentaHastaExclusivo($c->id, $inicio->format('Y-m-d'));
            $mov = $this->movimientosMes($c->id, $inicio->format('Y-m-d'), $fin->format('Y-m-d'));
            // Convención funcional solicitada en UI: Debe = gastos, Haber = ingresos.
            $gastos = $mov['debe'];
            $ingresos = $mov['haber'];
            $saldoFin = round($saldoIni - $gastos + $ingresos, 2);
            $filas[] = [
                'cuenta' => $c,
                'saldo_inicial' => round($saldoIni, 2),
                'ingresos' => round($ingresos, 2),
                'gastos' => round($gastos, 2),
                'saldo_final' => $saldoFin,
            ];
            $totIni += $saldoIni;
            $totIng += $ingresos;
            $totGas += $gastos;
            $totFin += $saldoFin;
        }

        return [
            'mes_inicio' => $inicio->format('Y-m-d'),
            'mes_fin' => $fin->format('Y-m-d'),
            'etiqueta_mes' => ucfirst($inicio->locale('es')->isoFormat('MMMM YYYY')),
            'cuentas' => $filas,
            'totales' => [
                'saldo_inicial' => round($totIni, 2),
                'ingresos' => round($totIng, 2),
                'gastos' => round($totGas, 2),
                'saldo_final' => round($totFin, 2),
            ],
        ];
    }

    private function saldoCuentaHastaExclusivo(int $cuentaId, string $fechaDesde): float
    {
        $row = Apunte::query()
            ->join('asientos', 'asientos.id', '=', 'apuntes.asiento_id')
            ->where('apuntes.cuenta_contable_id', $cuentaId)
            ->whereDate('asientos.fecha', '<', $fechaDesde)
            ->selectRaw('COALESCE(SUM(apuntes.debe - apuntes.haber), 0) as saldo')
            ->first();

        return (float) ($row->saldo ?? 0);
    }

    /**
     * @return array{debe: float, haber: float}
     */
    private function movimientosMes(int $cuentaId, string $desde, string $hasta): array
    {
        $row = Apunte::query()
            ->join('asientos', 'asientos.id', '=', 'apuntes.asiento_id')
            ->where('apuntes.cuenta_contable_id', $cuentaId)
            ->whereDate('asientos.fecha', '>=', $desde)
            ->whereDate('asientos.fecha', '<=', $hasta)
            ->selectRaw('COALESCE(SUM(apuntes.debe), 0) as debe, COALESCE(SUM(apuntes.haber), 0) as haber')
            ->first();

        return [
            'debe' => (float) ($row->debe ?? 0),
            'haber' => (float) ($row->haber ?? 0),
        ];
    }
}
