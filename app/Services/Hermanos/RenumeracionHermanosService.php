<?php

namespace App\Services\Hermanos;

use App\Models\Hermano;
use Illuminate\Support\Facades\DB;

class RenumeracionHermanosService
{
    /**
     * Reasigna numero_hermano de 1..N por fecha_alta (cronológico), luego id.
     * Usa números temporales altos para evitar conflictos de unicidad.
     *
     * @throws \RuntimeException
     */
    public function recalcularSinHuecos(): int
    {
        return (int) DB::transaction(function (): int {
            $ids = Hermano::query()
                ->orderByRaw('fecha_alta IS NULL')
                ->orderBy('fecha_alta')
                ->orderBy('id')
                ->pluck('id')
                ->all();

            if ($ids === []) {
                throw new \RuntimeException('No hay hermanos en el registro.');
            }

            $baseTemp = 900_000;

            foreach ($ids as $i => $id) {
                Hermano::query()->whereKey($id)->update([
                    'numero_hermano' => $baseTemp + $i,
                ]);
            }

            foreach ($ids as $i => $id) {
                Hermano::query()->whereKey($id)->update([
                    'numero_hermano' => $i + 1,
                ]);
            }

            return count($ids);
        });
    }
}
