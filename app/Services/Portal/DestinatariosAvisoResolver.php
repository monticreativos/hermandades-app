<?php

namespace App\Services\Portal;

use App\Models\Aviso;
use App\Models\Hermano;
use Illuminate\Support\Collection;

class DestinatariosAvisoResolver
{
    /**
     * @return Collection<int, Hermano>
     */
    public function hermanosPara(Aviso $aviso): Collection
    {
        return match ($aviso->alcance) {
            Aviso::ALCANCE_MASIVO => $this->masivo($aviso),
            Aviso::ALCANCE_INDIVIDUAL => $this->individual($aviso),
            Aviso::ALCANCE_SELECTIVO => collect(),
            default => collect(),
        };
    }

    /**
     * @param  array<int, int|string>  $ids
     * @return Collection<int, Hermano>
     */
    public function hermanosPorIds(array $ids): Collection
    {
        $ids = array_values(array_unique(array_map('intval', $ids)));

        return Hermano::query()->whereIn('id', $ids)->get();
    }

    /**
     * @return Collection<int, Hermano>
     */
    private function masivo(Aviso $aviso): Collection
    {
        $q = Hermano::query();

        if ($aviso->solo_alta) {
            $q->where('estado', 'Alta');
        }

        if ($aviso->solo_portal) {
            $q->whereHas('portalCuenta');
        }

        return $q->get();
    }

    /**
     * @return Collection<int, Hermano>
     */
    private function individual(Aviso $aviso): Collection
    {
        if (! $aviso->hermano_id) {
            return collect();
        }

        $h = Hermano::query()->find($aviso->hermano_id);

        return $h ? collect([$h]) : collect();
    }
}
