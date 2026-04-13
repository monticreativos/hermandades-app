<?php

namespace App\Exports;

use App\Models\Hermano;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class HermanosListadoPersonalizadoExport implements FromCollection, WithHeadings, WithMapping
{
    /** @var list<string> */
    private array $columnasOrden;

    /**
     * @param  list<string>  $columnasOrden
     */
    public function __construct(
        private readonly Collection $hermanos,
        array $columnasOrden
    ) {
        $this->columnasOrden = array_values($columnasOrden);
    }

    public function collection(): Collection
    {
        return $this->hermanos;
    }

    /**
     * @return list<string>
     */
    public function headings(): array
    {
        $map = [
            'numero_hermano' => 'N.º hermano',
            'nombre_completo' => 'Nombre y apellidos',
            'telefono' => 'Teléfono',
            'email' => 'Email',
            'antiguedad' => 'Antigüedad (años)',
            'dni' => 'DNI/NIE',
            'codigo_postal' => 'Código postal',
            'localidad' => 'Localidad',
            'estado' => 'Estado',
        ];

        return array_map(fn (string $c) => $map[$c] ?? $c, $this->columnasOrden);
    }

    /**
     * @param  Hermano  $hermano
     * @return list<int|string|null>
     */
    public function map($hermano): array
    {
        $ref = Carbon::now()->startOfDay();

        return array_map(function (string $col) use ($hermano, $ref) {
            return match ($col) {
                'numero_hermano' => $hermano->numero_hermano,
                'nombre_completo' => trim($hermano->apellidos.', '.$hermano->nombre),
                'telefono' => $hermano->telefono,
                'email' => $hermano->email,
                'antiguedad' => $hermano->fecha_alta
                    ? (int) $hermano->fecha_alta->diffInYears($ref)
                    : null,
                'dni' => $hermano->dni,
                'codigo_postal' => $hermano->codigo_postal,
                'localidad' => $hermano->localidad,
                'estado' => $hermano->estado,
                default => null,
            };
        }, $this->columnasOrden);
    }
}
