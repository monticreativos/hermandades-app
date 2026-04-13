<?php

namespace App\Services\Informes;

use App\Models\Hermano;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

class CensoElectoralService
{
    /**
     * Hermanos con derecho a voto según criterios eclesiásticos / reglamento interno.
     *
     * @param  bool  $excluirMorosos  Si true, excluye quien tenga participaciones de lotería sin cobrar.
     */
    public function queryVotantes(Carbon $fechaInforme, int $antiguedadAnos, bool $excluirMorosos): Builder
    {
        $limiteNacimiento = $fechaInforme->copy()->startOfDay()->subYears(18);
        $limiteAlta = $fechaInforme->copy()->startOfDay()->subYears(max(0, $antiguedadAnos));

        $q = Hermano::query()
            ->where('estado', 'Alta')
            ->whereNotNull('fecha_nacimiento')
            ->whereDate('fecha_nacimiento', '<=', $limiteNacimiento)
            ->whereNotNull('fecha_alta')
            ->whereDate('fecha_alta', '<=', $limiteAlta);

        if ($excluirMorosos) {
            $q->where(function (Builder $w): void {
                $w->whereNull('estado_cuota')
                    ->orWhereNotIn('estado_cuota', ['Pendiente', 'Impagada']);
            })->whereDoesntHave('loteriaAsignaciones', function (Builder $sub): void {
                $sub->where('cobrado', false);
            });
        }

        return $q->orderBy('apellidos')->orderBy('nombre')->orderBy('numero_hermano');
    }

    public function enmascararDni(?string $dni): string
    {
        $dni = trim((string) $dni);
        if ($dni === '') {
            return '—';
        }
        $len = mb_strlen($dni);
        if ($len <= 2) {
            return str_repeat('●', $len);
        }

        return str_repeat('●', $len - 2).mb_substr($dni, -2);
    }
}
