<?php

namespace App\Services\Sistema;

use App\Models\Ejercicio;
use App\Models\Hermano;
use App\Models\Proveedor;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;

class SaludSistemaChecker
{
    /**
     * @return array{ok: bool, mensaje: string}
     */
    public function enlaceStoragePublico(): array
    {
        $link = public_path('storage');
        if (! File::exists($link)) {
            return [
                'ok' => false,
                'mensaje' => 'No existe public/storage. Ejecute php artisan storage:link en el servidor.',
            ];
        }

        if (! File::isReadable($link)) {
            return [
                'ok' => false,
                'mensaje' => 'public/storage no es legible por el servidor web.',
            ];
        }

        return [
            'ok' => true,
            'mensaje' => 'Enlace o ruta public/storage accesible.',
        ];
    }

    /**
     * @return array{ok: bool, mensaje: string, abiertos: int, cerrados: int}
     */
    public function ejerciciosContables(): array
    {
        $abiertos = Ejercicio::query()->where('estado', Ejercicio::ESTADO_ABIERTO)->count();
        $cerrados = Ejercicio::query()->where('estado', Ejercicio::ESTADO_CERRADO)->count();

        return [
            'ok' => $abiertos > 0,
            'mensaje' => $abiertos > 0
                ? "Hay {$abiertos} ejercicio(s) abierto(s) y {$cerrados} cerrado(s)."
                : 'No hay ningún ejercicio contable en estado Abierto. Debe abrir el año en curso.',
            'abiertos' => $abiertos,
            'cerrados' => $cerrados,
        ];
    }

    /**
     * Hermanos con datos mínimos RGPD / censo incompletos.
     *
     * @return Collection<int, Hermano>
     */
    public function hermanosDatosIncompletos()
    {
        return Hermano::query()
            ->where(function ($q): void {
                $q->whereNull('dni')
                    ->orWhereRaw("TRIM(dni) = ''")
                    ->orWhereNull('fecha_nacimiento');
            })
            ->orderBy('numero_hermano')
            ->limit(200)
            ->get(['id', 'numero_hermano', 'nombre', 'apellidos', 'dni', 'fecha_nacimiento', 'estado']);
    }

    /**
     * @return array{
     *   ok: bool,
     *   hermanos_sin: int,
     *   proveedores_sin: int,
     *   hermanos_muestra: Collection<int, Hermano>,
     *   proveedores_muestra: Collection<int, Proveedor>
     * }
     */
    public function cuentasAuxiliaresPendientes(): array
    {
        $hSin = Hermano::query()->whereNull('cuenta_contable_id')->count();
        $pSin = Proveedor::query()->whereNull('cuenta_contable_id')->count();

        return [
            'ok' => $hSin === 0 && $pSin === 0,
            'hermanos_sin' => $hSin,
            'proveedores_sin' => $pSin,
            'hermanos_muestra' => Hermano::query()
                ->whereNull('cuenta_contable_id')
                ->orderBy('numero_hermano')
                ->limit(12)
                ->get(['id', 'numero_hermano', 'nombre', 'apellidos', 'estado']),
            'proveedores_muestra' => Proveedor::query()
                ->whereNull('cuenta_contable_id')
                ->orderBy('razon_social')
                ->limit(12)
                ->get(['id', 'razon_social', 'nif_cif']),
        ];
    }
}
