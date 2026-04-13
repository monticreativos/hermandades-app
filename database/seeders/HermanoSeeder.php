<?php

namespace Database\Seeders;

use App\Models\Banco;
use App\Models\Hermano;
use Illuminate\Database\Seeder;

class HermanoSeeder extends Seeder
{
    public function run(): void
    {
        $nombres = ['Jose', 'Maria', 'Antonio', 'Carmen', 'Manuel', 'Ana', 'Rafael', 'Lola', 'Francisco', 'Elena'];
        $apellidos = ['Garcia', 'Rodriguez', 'Fernandez', 'Gonzalez', 'Lopez', 'Martinez', 'Sanchez', 'Perez', 'Gomez', 'Ruiz'];

        for ($i = 1; $i <= 25; $i++) {
            $nombre = $nombres[array_rand($nombres)];
            $apellido1 = $apellidos[array_rand($apellidos)];
            $apellido2 = $apellidos[array_rand($apellidos)];
            $dni = $this->generarDni($i);

            $estado = ['Alta', 'Baja', 'Difunto'][array_rand(['Alta', 'Baja', 'Difunto'])];
            $fechaAlta = now()->subYears(rand(1, 40))->toDateString();
            $fechaBaja = $estado === 'Alta' ? null : now()->subMonths(rand(1, 48))->toDateString();
            $banco = Banco::query()->inRandomOrder()->first();

            Hermano::query()->updateOrCreate(
                ['dni' => $dni],
                [
                    'numero_hermano' => $i,
                    'nombre' => $nombre,
                    'apellidos' => $apellido1.' '.$apellido2,
                    'fecha_nacimiento' => now()->subYears(rand(18, 85))->subDays(rand(0, 300)),
                    'sexo' => ['Hombre', 'Mujer', 'Otro'][array_rand(['Hombre', 'Mujer', 'Otro'])],
                    'direccion' => 'Calle Ejemplo '.$i,
                    'localidad' => 'Sevilla',
                    'provincia' => 'Sevilla',
                    'codigo_postal' => '4100'.rand(1, 9),
                    'telefono' => '6'.rand(10000000, 99999999),
                    'email' => 'hermano'.$i.'@test.local',
                    'banco_id' => $banco?->id,
                    'sucursal' => 'Sucursal '.rand(1, 12).' Sevilla',
                    'iban' => 'ES7620770024003102575766',
                    'titular_cuenta' => $nombre.' '.$apellido1,
                    'titular_cuenta_menor' => rand(0, 1) ? 'Padre/Madre '.$apellido1 : null,
                    'fecha_alta' => $fechaAlta,
                    'fecha_baja' => $fechaBaja,
                    'fecha_bautismo' => now()->subYears(rand(10, 80))->toDateString(),
                    'parroquia_bautismo' => 'Parroquia de San '.['Juan', 'Pedro', 'Pablo'][array_rand(['Juan', 'Pedro', 'Pablo'])],
                    'estado' => $estado,
                    'observaciones' => 'Registro de ejemplo generado por seeder.',
                ]
            );
        }
    }

    private function generarDni(int $n): string
    {
        $numero = str_pad((string) (10000000 + $n), 8, '0', STR_PAD_LEFT);
        $tabla = 'TRWAGMYFPDXBNJZSQVHLCKE';
        $letra = $tabla[((int) $numero) % 23];

        return $numero.$letra;
    }
}
