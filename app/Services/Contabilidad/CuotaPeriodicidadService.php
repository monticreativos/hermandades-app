<?php

namespace App\Services\Contabilidad;

use App\Models\ConfiguracionHermandad;
use App\Models\Hermano;
use App\Models\RemesaRecibo;
use Illuminate\Support\Collection;

class CuotaPeriodicidadService
{
    public const PERIODICIDAD_MENSUAL = 'Mensual';

    public const PERIODICIDAD_TRIMESTRAL = 'Trimestral';

    public const PERIODICIDAD_SEMESTRAL = 'Semestral';

    public const PERIODICIDAD_ANUAL = 'Anual';

    public static function periodicidades(): array
    {
        return [
            self::PERIODICIDAD_MENSUAL,
            self::PERIODICIDAD_TRIMESTRAL,
            self::PERIODICIDAD_SEMESTRAL,
            self::PERIODICIDAD_ANUAL,
        ];
    }

    public function importeAnualReferencia(Hermano $hermano): float
    {
        if ($hermano->importe_cuota_anual_referencia !== null && (float) $hermano->importe_cuota_anual_referencia > 0) {
            return round((float) $hermano->importe_cuota_anual_referencia, 2);
        }

        $def = ConfiguracionHermandad::query()->value('importe_cuota_anual_defecto');
        if ($def !== null && (float) $def > 0) {
            return round((float) $def, 2);
        }

        return 60.0;
    }

    /**
     * @return list<string>
     */
    public function periodosCobrados(Hermano $hermano): array
    {
        $raw = $hermano->periodos_cuota_cobrados_json;
        if ($raw === null || $raw === []) {
            return [];
        }
        if (is_string($raw)) {
            $raw = json_decode($raw, true) ?? [];
        }

        return is_array($raw) ? array_values(array_filter($raw, 'is_string')) : [];
    }

    public function marcarPeriodoCobrado(Hermano $hermano, string $periodoClave): void
    {
        $arr = $this->periodosCobrados($hermano);
        if (! in_array($periodoClave, $arr, true)) {
            $arr[] = $periodoClave;
            sort($arr);
            $hermano->forceFill(['periodos_cuota_cobrados_json' => $arr])->save();
        }
    }

    public function desmarcarPeriodoCobrado(Hermano $hermano, string $periodoClave): void
    {
        $arr = array_values(array_filter(
            $this->periodosCobrados($hermano),
            fn (string $p) => $p !== $periodoClave
        ));
        $hermano->forceFill(['periodos_cuota_cobrados_json' => $arr])->save();
    }

    /**
     * Líneas a incluir en la remesa del mes/año indicados (incluye atrasados no cobrados).
     *
     * @return Collection<int, array{hermano: Hermano, periodo_clave: string, importe: float, end_to_end_id: string, concepto: string}>
     */
    public function lineasParaRemesa(int $año, int $mes, Collection $hermanosCandidatos): Collection
    {
        $lineas = collect();

        foreach ($hermanosCandidatos as $hermano) {
            foreach ($this->periodosPendientesHasta($hermano, $año, $mes) as $row) {
                if ($this->existeReciboPendienteBanco($hermano->id, $row['periodo_clave'])) {
                    continue;
                }
                $e2e = $this->construirEndToEndId($hermano->id, $row['periodo_clave']);
                $lineas->push([
                    'hermano' => $hermano,
                    'periodo_clave' => $row['periodo_clave'],
                    'importe' => $row['importe'],
                    'end_to_end_id' => $e2e,
                    'concepto' => 'Cuota '.$row['periodo_clave'].' — n.º '.$hermano->numero_hermano,
                ]);
            }
        }

        return $lineas;
    }

    /**
     * @return list<array{periodo_clave: string, importe: float}>
     */
    public function periodosPendientesHasta(Hermano $hermano, int $año, int $mes): array
    {
        $annual = $this->importeAnualReferencia($hermano);
        $cobrados = $this->periodosCobrados($hermano);
        $per = $hermano->periodicidad_pago ?: self::PERIODICIDAD_MENSUAL;

        $pendientes = [];

        foreach ($this->clavesTeoricasHastaMes($per, $año, $mes) as $clave) {
            if (in_array($clave, $cobrados, true)) {
                continue;
            }
            $imp = $this->importeParaClave($per, $annual, $clave, $año);
            if ($imp > 0) {
                $pendientes[] = ['periodo_clave' => $clave, 'importe' => $imp];
            }
        }

        return $pendientes;
    }

    /**
     * @return list<string>
     */
    private function clavesTeoricasHastaMes(string $periodicidad, int $año, int $mesHasta): array
    {
        $mesHasta = max(1, min(12, $mesHasta));
        $claves = [];

        if ($periodicidad === self::PERIODICIDAD_MENSUAL) {
            for ($m = 1; $m <= $mesHasta; $m++) {
                $claves[] = sprintf('%04d-%02d', $año, $m);
            }

            return $claves;
        }

        if ($periodicidad === self::PERIODICIDAD_TRIMESTRAL) {
            for ($q = 1; $q <= 4; $q++) {
                if ($this->mesCargoTrimestre($q) <= $mesHasta) {
                    $claves[] = sprintf('%04d-Q%d', $año, $q);
                }
            }

            return $claves;
        }

        if ($periodicidad === self::PERIODICIDAD_SEMESTRAL) {
            for ($s = 1; $s <= 2; $s++) {
                if ($this->mesCargoSemestre($s) <= $mesHasta) {
                    $claves[] = sprintf('%04d-S%d', $año, $s);
                }
            }

            return $claves;
        }

        if ($periodicidad === self::PERIODICIDAD_ANUAL) {
            if ($mesHasta >= 1) {
                $claves[] = sprintf('%04d-A', $año);
            }
        }

        return $claves;
    }

    private function mesCargoTrimestre(int $q): int
    {
        return match ($q) {
            1 => 1,
            2 => 4,
            3 => 7,
            4 => 10,
            default => 13,
        };
    }

    private function mesCargoSemestre(int $s): int
    {
        return $s === 1 ? 1 : 7;
    }

    private function importeParaClave(string $periodicidad, float $annual, string $clave, int $año): float
    {
        return match ($periodicidad) {
            self::PERIODICIDAD_MENSUAL => round($annual / 12, 2),
            self::PERIODICIDAD_TRIMESTRAL => round($annual / 4, 2),
            self::PERIODICIDAD_SEMESTRAL => round($annual / 2, 2),
            self::PERIODICIDAD_ANUAL => round($annual, 2),
            default => round($annual / 12, 2),
        };
    }

    public function construirEndToEndId(int $hermanoId, string $periodoClave): string
    {
        $safe = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $periodoClave) ?: 'X');

        return 'E2E-H'.$hermanoId.'-'.$safe;
    }

    private function existeReciboPendienteBanco(int $hermanoId, string $periodoClave): bool
    {
        return RemesaRecibo::query()
            ->where('hermano_id', $hermanoId)
            ->where('periodo_clave', $periodoClave)
            ->where('estado', RemesaRecibo::ESTADO_PENDIENTE_BANCO)
            ->exists();
    }
}
