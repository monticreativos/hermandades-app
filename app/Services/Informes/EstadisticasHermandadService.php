<?php

namespace App\Services\Informes;

use App\Models\Hermano;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class EstadisticasHermandadService
{
    /**
     * @return array{
     *   kpis: array{total_alta: int, pct_voto: float, pct_morosidad: float},
     *   piramide: array<int, array{clave: string, etiqueta: string, total: int, pct: float}>,
     *   altas_bajas: array<int, array{año: int, altas: int, bajas: int, neto: int}>,
     *   top_cp: array<int, array{cp: string, total: int}>
     * }
     */
    public function resumen(Carbon $ref, int $antiguedadVotoAnos): array
    {
        $totalAlta = Hermano::query()->where('estado', 'Alta')->count();

        $censo = new CensoElectoralService;
        $votantes = (clone $censo->queryVotantes($ref, $antiguedadVotoAnos, false))->count();
        $pctVoto = $totalAlta > 0 ? round(100 * $votantes / $totalAlta, 1) : 0.0;

        $conMorosidad = Hermano::query()
            ->where('estado', 'Alta')
            ->where(function ($q): void {
                $q->whereIn('estado_cuota', ['Pendiente', 'Impagada'])
                    ->orWhereHas('loteriaAsignaciones', function ($sub): void {
                        $sub->where('cobrado', false);
                    });
            })
            ->count();
        $pctMorosidad = $totalAlta > 0 ? round(100 * $conMorosidad / $totalAlta, 1) : 0.0;

        $piramide = $this->piramideEdad($ref);
        $altasBajas = $this->altasBajasUltimosAnos($ref, 3);
        $topCp = $this->topCodigosPostales();

        return [
            'kpis' => [
                'total_alta' => $totalAlta,
                'pct_voto' => $pctVoto,
                'pct_morosidad' => $pctMorosidad,
            ],
            'piramide' => $piramide,
            'altas_bajas' => $altasBajas,
            'top_cp' => $topCp,
        ];
    }

    /**
     * @return array<int, array{clave: string, etiqueta: string, total: int, pct: float}>
     */
    private function piramideEdad(Carbon $ref): array
    {
        $fechas = Hermano::query()
            ->where('estado', 'Alta')
            ->whereNotNull('fecha_nacimiento')
            ->pluck('fecha_nacimiento');

        $r0 = $r1 = $r2 = $r3 = 0;
        foreach ($fechas as $fn) {
            $nac = Carbon::parse($fn)->startOfDay();
            $edad = (int) $nac->diffInYears($ref);
            if ($edad <= 14) {
                $r0++;
            } elseif ($edad <= 25) {
                $r1++;
            } elseif ($edad <= 65) {
                $r2++;
            } else {
                $r3++;
            }
        }

        $totalConEdad = $r0 + $r1 + $r2 + $r3;
        if ($totalConEdad === 0) {
            return [
                $this->filaPiramide('0-14', 'Paveros / infantiles (0–14)', 0, 0.0),
                $this->filaPiramide('15-25', 'Juventud (15–25)', 0, 0.0),
                $this->filaPiramide('26-65', 'Adultos (26–65)', 0, 0.0),
                $this->filaPiramide('66+', 'Veteranos (+65)', 0, 0.0),
            ];
        }

        return [
            $this->filaPiramide('0-14', 'Paveros / infantiles (0–14)', $r0, round(100 * $r0 / $totalConEdad, 1)),
            $this->filaPiramide('15-25', 'Juventud (15–25)', $r1, round(100 * $r1 / $totalConEdad, 1)),
            $this->filaPiramide('26-65', 'Adultos (26–65)', $r2, round(100 * $r2 / $totalConEdad, 1)),
            $this->filaPiramide('66+', 'Veteranos (+65)', $r3, round(100 * $r3 / $totalConEdad, 1)),
        ];
    }

    /**
     * @return array{clave: string, etiqueta: string, total: int, pct: float}
     */
    private function filaPiramide(string $clave, string $etiqueta, int $total, float $pct): array
    {
        return [
            'clave' => $clave,
            'etiqueta' => $etiqueta,
            'total' => $total,
            'pct' => $pct,
        ];
    }

    /**
     * @return array<int, array{año: int, altas: int, bajas: int, neto: int}>
     */
    private function altasBajasUltimosAnos(Carbon $ref, int $n): array
    {
        $añoActual = (int) $ref->year;
        $out = [];
        for ($i = $n - 1; $i >= 0; $i--) {
            $y = $añoActual - $i;
            $altas = Hermano::query()->whereYear('fecha_alta', $y)->count();
            $bajas = Hermano::query()
                ->whereNotNull('fecha_baja')
                ->whereYear('fecha_baja', $y)
                ->whereIn('estado', ['Baja', 'Difunto'])
                ->count();
            $out[] = [
                'año' => $y,
                'altas' => $altas,
                'bajas' => $bajas,
                'neto' => $altas - $bajas,
            ];
        }

        return $out;
    }

    /**
     * @return array<int, array{cp: string, total: int}>
     */
    private function topCodigosPostales(): array
    {
        $rows = Hermano::query()
            ->where('estado', 'Alta')
            ->whereNotNull('codigo_postal')
            ->where('codigo_postal', '!=', '')
            ->select('codigo_postal', DB::raw('COUNT(*) as total'))
            ->groupBy('codigo_postal')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        return $rows->map(fn ($r) => [
            'cp' => (string) $r->codigo_postal,
            'total' => (int) $r->total,
        ])->all();
    }
}
