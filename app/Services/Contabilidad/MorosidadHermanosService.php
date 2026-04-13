<?php

namespace App\Services\Contabilidad;

use App\Models\Apunte;
use App\Models\Hermano;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class MorosidadHermanosService
{
    /**
     * Hermanos con saldo deudor en subcuenta 430/431 (debe − haber > 0).
     *
     * @return Collection<int, array{
     *   hermano: Hermano,
     *   saldo: float,
     *   cuenta_codigo: string|null,
     *   fecha_inicio_deuda: Carbon|null,
     *   dias_mora: int|null
     * }>
     */
    public function listado(?string $filtroAntiguedad = null): Collection
    {
        $hermanos = Hermano::query()
            ->with('cuentaContable')
            ->whereNotNull('cuenta_contable_id')
            ->orderBy('numero_hermano')
            ->get();

        $cuentaIds = $hermanos->pluck('cuenta_contable_id')->filter()->unique()->values();
        if ($cuentaIds->isEmpty()) {
            return collect();
        }

        $saldos = Apunte::query()
            ->join('asientos', 'asientos.id', '=', 'apuntes.asiento_id')
            ->whereIn('apuntes.cuenta_contable_id', $cuentaIds->all())
            ->groupBy('apuntes.cuenta_contable_id')
            ->selectRaw('apuntes.cuenta_contable_id, SUM(apuntes.debe - apuntes.haber) as saldo')
            ->pluck('saldo', 'cuenta_contable_id');

        $out = collect();
        foreach ($hermanos as $h) {
            $cid = $h->cuenta_contable_id;
            if (! $cid) {
                continue;
            }
            $saldo = round((float) ($saldos[$cid] ?? 0), 2);
            if ($saldo <= 0.02) {
                continue;
            }
            $codigo = $h->cuentaContable?->codigo;
            if ($codigo && ! str_starts_with($codigo, '430') && ! str_starts_with($codigo, '431')) {
                continue;
            }
            $fechaIni = $this->fechaInicioDeudaActual((int) $cid);
            $dias = $fechaIni ? max(0, $fechaIni->diffInDays(Carbon::now())) : null;

            if ($filtroAntiguedad === '1y' && ($dias === null || $dias < 365)) {
                continue;
            }
            if ($filtroAntiguedad === '3y' && ($dias === null || $dias < 365 * 3)) {
                continue;
            }

            $out->push([
                'hermano' => $h,
                'saldo' => $saldo,
                'cuenta_codigo' => $codigo,
                'fecha_inicio_deuda' => $fechaIni,
                'dias_mora' => $dias,
            ]);
        }

        return $out->values();
    }

    /**
     * Inicio del período de saldo deudor actual (recorrido cronológico).
     */
    public function fechaInicioDeudaActual(int $cuentaContableId): ?Carbon
    {
        $rows = Apunte::query()
            ->join('asientos', 'asientos.id', '=', 'apuntes.asiento_id')
            ->where('apuntes.cuenta_contable_id', $cuentaContableId)
            ->orderBy('asientos.fecha')
            ->orderBy('asientos.numero_asiento')
            ->orderBy('asientos.ejercicio_id')
            ->orderBy('apuntes.id')
            ->get(['apuntes.debe', 'apuntes.haber', 'asientos.fecha as fecha_asiento']);

        $saldo = 0.0;
        $debtStart = null;
        foreach ($rows as $row) {
            $saldo += (float) $row->debe - (float) $row->haber;
            if ($saldo > 0.02) {
                if ($debtStart === null) {
                    $debtStart = Carbon::parse($row->fecha_asiento);
                }
            } else {
                $debtStart = null;
            }
        }

        if ($saldo <= 0.02) {
            return null;
        }

        return $debtStart;
    }
}
