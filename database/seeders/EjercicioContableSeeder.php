<?php

namespace Database\Seeders;

use App\Models\Ejercicio;
use Illuminate\Database\Seeder;

class EjercicioContableSeeder extends Seeder
{
    public function run(): void
    {
        $añoActual = (int) now()->year;
        $añoAnterior = $añoActual - 1;

        Ejercicio::query()->updateOrCreate(
            ['año' => $añoAnterior],
            ['estado' => Ejercicio::ESTADO_CERRADO]
        );

        Ejercicio::query()->updateOrCreate(
            ['año' => $añoActual],
            ['estado' => Ejercicio::ESTADO_ABIERTO]
        );
    }
}
