<?php

namespace Database\Seeders;

use App\Models\Hermano;
use App\Models\HermanoPortalCuenta;
use Illuminate\Database\Seeder;

class HermanoPortalSeeder extends Seeder
{
    /**
     * Crea una cuenta del Portal del Hermano enlazada al hermano n.º 1 (local / pruebas).
     */
    public const PASSWORD_PLANO_PRUEBA = 'PortalHermano1!';

    public function run(): void
    {
        $hermano = Hermano::query()->where('numero_hermano', 1)->first()
            ?? Hermano::query()->orderBy('id')->first();

        if (! $hermano) {
            $this->command?->warn('HermanoPortalSeeder: no hay hermanos. Ejecute antes HermanoSeeder.');

            return;
        }

        if (blank($hermano->email)) {
            $this->command?->warn('HermanoPortalSeeder: el hermano elegido no tiene email.');

            return;
        }

        HermanoPortalCuenta::query()->updateOrCreate(
            ['hermano_id' => $hermano->id],
            [
                'email' => $hermano->email,
                'password' => self::PASSWORD_PLANO_PRUEBA,
                'email_verified_at' => now(),
                'activacion_token_hash' => null,
                'activacion_expira' => null,
                'activacion_codigo_hash' => null,
                'activacion_codigo_expira' => null,
                'recuperacion_codigo_hash' => null,
                'recuperacion_expira' => null,
            ]
        );

        $hermano->forceFill([
            'rgpd_aceptado' => true,
            'rgpd_fecha' => now(),
            'rgpd_ip' => null,
        ])->save();
    }
}
