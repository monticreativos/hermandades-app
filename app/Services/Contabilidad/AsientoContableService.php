<?php

namespace App\Services\Contabilidad;

use App\Models\Apunte;
use App\Models\Asiento;
use App\Models\Ejercicio;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AsientoContableService
{
    public function ejercicioParaFecha(Carbon $fecha): Ejercicio
    {
        $ejercicio = Ejercicio::query()->where('año', $fecha->year)->firstOrFail();

        if (! $ejercicio->estaAbierto()) {
            throw new \RuntimeException('El ejercicio contable del año '.$fecha->year.' está cerrado.');
        }

        return $ejercicio;
    }

    public function siguienteNumeroAsiento(Ejercicio $ejercicio): int
    {
        $max = Asiento::query()->where('ejercicio_id', $ejercicio->id)->max('numero_asiento');

        return (int) $max + 1;
    }

    /**
     * @param  array<int, array{cuenta_contable_id: int, debe: string|float, haber: string|float, concepto_detalle?: string|null}>  $lineas
     * @param  array<string, mixed>  $atributosAsiento
     */
    public function crearAsiento(Ejercicio $ejercicio, string $fecha, string $glosa, array $lineas, array $atributosAsiento = []): Asiento
    {
        return DB::transaction(function () use ($ejercicio, $fecha, $glosa, $lineas, $atributosAsiento): Asiento {
            $asiento = Asiento::query()->create(array_merge([
                'ejercicio_id' => $ejercicio->id,
                'numero_asiento' => $this->siguienteNumeroAsiento($ejercicio),
                'fecha' => $fecha,
                'glosa' => $glosa,
            ], $atributosAsiento));

            foreach ($lineas as $linea) {
                Apunte::query()->create([
                    'asiento_id' => $asiento->id,
                    'cuenta_contable_id' => $linea['cuenta_contable_id'],
                    'debe' => $linea['debe'],
                    'haber' => $linea['haber'],
                    'concepto_detalle' => $linea['concepto_detalle'] ?? null,
                ]);
            }

            return $asiento->load('apuntes');
        });
    }

    /**
     * @param  array<int, array{cuenta_contable_id: int, debe: string|float, haber: string|float, concepto_detalle?: string|null}>  $lineas
     */
    public function actualizarAsiento(Asiento $asiento, string $fecha, string $glosa, array $lineas): Asiento
    {
        return DB::transaction(function () use ($asiento, $fecha, $glosa, $lineas): Asiento {
            $asiento->update([
                'fecha' => $fecha,
                'glosa' => $glosa,
            ]);

            $asiento->apuntes()->delete();

            foreach ($lineas as $linea) {
                Apunte::query()->create([
                    'asiento_id' => $asiento->id,
                    'cuenta_contable_id' => $linea['cuenta_contable_id'],
                    'debe' => $linea['debe'],
                    'haber' => $linea['haber'],
                    'concepto_detalle' => $linea['concepto_detalle'] ?? null,
                ]);
            }

            return $asiento->fresh(['apuntes']);
        });
    }

    public function eliminarAsiento(Asiento $asiento): void
    {
        DB::transaction(function () use ($asiento): void {
            $asiento->apuntes()->delete();
            $asiento->delete();
        });
    }
}
