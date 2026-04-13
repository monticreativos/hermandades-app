<?php

namespace App\Services\Informes;

use App\Models\Hermano;
use Illuminate\Support\Collection;

class EtiquetasMailingService
{
    /**
     * @return Collection<int, Hermano>
     */
    public function hermanosParaEtiquetas(string $modo, ?string $codigoPostalFiltro): Collection
    {
        $query = Hermano::query()
            ->where('estado', 'Alta')
            ->orderBy('apellidos')
            ->orderBy('nombre')
            ->orderBy('numero_hermano');

        if ($codigoPostalFiltro !== null && $codigoPostalFiltro !== '') {
            $cp = trim($codigoPostalFiltro);
            $query->where('codigo_postal', 'like', $cp.'%');
        }

        $coleccion = $query->get();

        if ($modo !== 'cabezas') {
            return $coleccion;
        }

        return $this->filtrarCabezasDeFamilia($coleccion);
    }

    /**
     * Una etiqueta por domicilio (dirección + CP); el representante es el de menor número de hermano.
     *
     * @param  Collection<int, Hermano>  $hermanos
     * @return Collection<int, Hermano>
     */
    public function filtrarCabezasDeFamilia(Collection $hermanos): Collection
    {
        return $hermanos
            ->groupBy(fn (Hermano $h) => $this->claveDomicilio($h))
            ->map(function (Collection $grupo): Hermano {
                return $grupo->sortBy([
                    ['numero_hermano', 'asc'],
                    ['id', 'asc'],
                ])->first();
            })
            ->values();
    }

    private function claveDomicilio(Hermano $h): string
    {
        $d = mb_strtolower(trim((string) $h->direccion));
        $cp = trim((string) $h->codigo_postal);
        $loc = mb_strtolower(trim((string) $h->localidad));

        if ($d === '' && $cp === '') {
            return 'único_'.$h->id;
        }

        return $d.'|'.$cp.'|'.$loc;
    }
}
