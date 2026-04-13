<?php

namespace App\Services\Contabilidad;

use App\Models\Asiento;
use App\Models\CuentaContable;
use App\Models\Hermano;
use Illuminate\Support\Collection;

class CuotaHermanoEstadoService
{
    public const ESTADO_AL_CORRIENTE = 'Al_corriente';

    public const ESTADO_PENDIENTE = 'Pendiente';

    public const ESTADO_IMPAGADA = 'Impagada';

    /**
     * Tras generar el asiento masivo de cuotas desde Economía (debe en 430/431).
     *
     * @param  Collection<int, Hermano>  $hermanos
     */
    public function marcarPendientesTrasEmisionMasiva(Collection $hermanos, int $ejercicioId): void
    {
        $ids = $hermanos->pluck('id')->filter()->all();
        if ($ids === []) {
            return;
        }

        Hermano::query()
            ->whereIn('id', $ids)
            ->where('estado', 'Alta')
            ->update([
                'estado_cuota' => self::ESTADO_PENDIENTE,
                'cuota_pendiente_ejercicio_id' => $ejercicioId,
            ]);
    }

    /**
     * Asiento de cobro: Debe bancos (572) y Haber deudores cofrades (430/431).
     */
    public function esAsientoCobroCuotas(Asiento $asiento): bool
    {
        $apuntes = $asiento->relationLoaded('apuntes')
            ? $asiento->apuntes
            : $asiento->apuntes()->with('cuentaContable')->get();

        $tieneBancoDebe = false;
        $tieneDeudorHaber = false;

        foreach ($apuntes as $apunte) {
            $codigo = $apunte->cuentaContable?->codigo ?? '';
            $debe = (float) $apunte->debe;
            $haber = (float) $apunte->haber;

            if ($this->esCuentaBancoCajaVista($codigo) && $debe > 0.004) {
                $tieneBancoDebe = true;
            }
            if ($this->esCuentaDeudorCofrade($codigo) && $haber > 0.004) {
                $tieneDeudorHaber = true;
            }
        }

        return $tieneBancoDebe && $tieneDeudorHaber;
    }

    public function aplicarCobroCuotasSiProcede(Asiento $asiento): void
    {
        if (! $this->esAsientoCobroCuotas($asiento)) {
            return;
        }

        $numeros = $this->extraerNumerosHermanosDesdeHaberDeudores($asiento);
        if ($numeros === []) {
            return;
        }

        $ejercicioId = (int) $asiento->ejercicio_id;

        foreach ($numeros as $numeroHermano) {
            $hermano = Hermano::query()->where('numero_hermano', $numeroHermano)->first();
            if (! $hermano) {
                continue;
            }

            if ($hermano->estado_cuota === self::ESTADO_PENDIENTE
                && (int) $hermano->cuota_pendiente_ejercicio_id === $ejercicioId) {
                $hermano->update([
                    'estado_cuota' => self::ESTADO_AL_CORRIENTE,
                    'cuota_pendiente_ejercicio_id' => null,
                ]);
            }
        }
    }

    public function revertirCobroCuotasSiProcede(Asiento $asiento): void
    {
        if (! $this->esAsientoCobroCuotas($asiento)) {
            return;
        }

        $numeros = $this->extraerNumerosHermanosDesdeHaberDeudores($asiento);
        if ($numeros === []) {
            return;
        }

        $ejercicioId = (int) $asiento->ejercicio_id;

        foreach ($numeros as $numeroHermano) {
            $hermano = Hermano::query()->where('numero_hermano', $numeroHermano)->first();
            if (! $hermano || $hermano->estado !== 'Alta') {
                continue;
            }

            if ($hermano->estado_cuota === self::ESTADO_AL_CORRIENTE) {
                $hermano->update([
                    'estado_cuota' => self::ESTADO_PENDIENTE,
                    'cuota_pendiente_ejercicio_id' => $ejercicioId,
                ]);
            }
        }
    }

    /**
     * @return list<int>
     */
    private function extraerNumerosHermanosDesdeHaberDeudores(Asiento $asiento): array
    {
        $apuntes = $asiento->relationLoaded('apuntes')
            ? $asiento->apuntes
            : $asiento->apuntes()->with('cuentaContable')->get();

        $numeros = [];

        foreach ($apuntes as $apunte) {
            $codigo = $apunte->cuentaContable?->codigo ?? '';
            $haber = (float) $apunte->haber;
            if (! $this->esCuentaDeudorCofrade($codigo) || $haber <= 0.004) {
                continue;
            }
            $numPorCuenta = Hermano::query()
                ->where('cuenta_contable_id', $apunte->cuenta_contable_id)
                ->value('numero_hermano');
            if ($numPorCuenta !== null) {
                $numeros[(int) $numPorCuenta] = true;
            }
            $concepto = (string) ($apunte->concepto_detalle ?? '');
            foreach ($this->parseNumerosHermanoDesdeTexto($concepto) as $n) {
                $numeros[$n] = true;
            }
        }

        return array_map('intval', array_keys($numeros));
    }

    /**
     * @return list<int>
     */
    private function parseNumerosHermanoDesdeTexto(string $texto): array
    {
        if ($texto === '') {
            return [];
        }

        if (preg_match_all('/n\.?\s*º\s*(\d+)/iu', $texto, $m)) {
            return array_map('intval', $m[1]);
        }

        return [];
    }

    private function esCuentaBancoCajaVista(string $codigo): bool
    {
        return str_starts_with($codigo, '572') || str_starts_with($codigo, '570');
    }

    private function esCuentaDeudorCofrade(string $codigo): bool
    {
        return str_starts_with($codigo, '431') || str_starts_with($codigo, '430');
    }

    public function cuentaEsDeudorCofrade(CuentaContable $cuenta): bool
    {
        return $this->esCuentaDeudorCofrade($cuenta->codigo);
    }

    public function marcarImpagadaPorDevolucionRemesa(Hermano $hermano, Ejercicio $ejercicio, ?string $motivo = null): void
    {
        $hermano->forceFill([
            'estado_cuota' => self::ESTADO_IMPAGADA,
            'cuota_pendiente_ejercicio_id' => $ejercicio->id,
        ])->save();
    }

    public function refrescarEstadoSegunPeriodicidad(Hermano $hermano, Ejercicio $ejercicio, CuotaPeriodicidadService $periodicidad): void
    {
        $año = (int) $ejercicio->año;
        $pendientes = $periodicidad->periodosPendientesHasta($hermano, $año, 12);

        if ($pendientes === []) {
            $hermano->forceFill([
                'estado_cuota' => self::ESTADO_AL_CORRIENTE,
                'cuota_pendiente_ejercicio_id' => null,
            ])->save();

            return;
        }

        $hermano->forceFill([
            'estado_cuota' => self::ESTADO_PENDIENTE,
            'cuota_pendiente_ejercicio_id' => $ejercicio->id,
        ])->save();
    }
}
