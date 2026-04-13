<?php

namespace App\Services\Portal;

use App\Models\Hermano;
use App\Models\HermanoPortalCuenta;
use App\Models\SolicitudCambioDatos;
use App\Models\User;
use App\Support\RegistroActividad;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ProcesarSolicitudCambioDatosService
{
    private const CAMPOS_PERMITIDOS = [
        'direccion',
        'localidad',
        'provincia',
        'codigo_postal',
        'telefono',
        'email',
        'iban',
    ];

    public function aprobar(SolicitudCambioDatos $solicitud, User $usuario): void
    {
        if (! $solicitud->estaPendiente()) {
            throw ValidationException::withMessages([
                'solicitud' => ['Esta solicitud ya fue procesada.'],
            ]);
        }

        $datos = $solicitud->datos_solicitados;
        if (! is_array($datos) || $datos === []) {
            throw ValidationException::withMessages([
                'solicitud' => ['La solicitud no contiene datos válidos.'],
            ]);
        }

        $numeroHermano = null;

        DB::transaction(function () use ($solicitud, $usuario, $datos, &$numeroHermano): void {
            /** @var Hermano $hermano */
            $hermano = $solicitud->hermano()->lockForUpdate()->firstOrFail();
            $numeroHermano = $hermano->numero_hermano;

            foreach ($datos as $campo => $par) {
                if (! in_array($campo, self::CAMPOS_PERMITIDOS, true)) {
                    continue;
                }
                if (! is_array($par) || ! array_key_exists('despues', $par)) {
                    continue;
                }
                $valor = $par['despues'];
                if ($campo === 'iban' && is_string($valor)) {
                    $valor = str_replace(' ', '', strtoupper(trim($valor)));
                }
                $hermano->{$campo} = $valor;
            }

            $hermano->save();

            if (isset($datos['email']['despues']) && is_string($datos['email']['despues'])) {
                $emailNuevo = trim($datos['email']['despues']);
                HermanoPortalCuenta::query()
                    ->where('hermano_id', $hermano->id)
                    ->update([
                        'email' => $emailNuevo,
                        'email_verified_at' => null,
                    ]);
            }

            $solicitud->forceFill([
                'estado' => SolicitudCambioDatos::ESTADO_APROBADA,
                'motivo_rechazo' => null,
                'procesado_por_user_id' => $usuario->id,
                'procesado_en' => now(),
            ])->save();
        });

        RegistroActividad::registrar(
            'solicitud_datos_aprobada',
            'Aprobada solicitud de cambio de datos del hermano n.º '.$solicitud->hermano->numero_hermano.' (solicitud #'.$solicitud->id.').'
        );
    }

    public function rechazar(SolicitudCambioDatos $solicitud, User $usuario, string $motivo): void
    {
        if (! $solicitud->estaPendiente()) {
            throw ValidationException::withMessages([
                'solicitud' => ['Esta solicitud ya fue procesada.'],
            ]);
        }

        $numeroHermano = $solicitud->hermano->numero_hermano;

        $solicitud->forceFill([
            'estado' => SolicitudCambioDatos::ESTADO_RECHAZADA,
            'motivo_rechazo' => $motivo,
            'procesado_por_user_id' => $usuario->id,
            'procesado_en' => now(),
        ])->save();

        RegistroActividad::registrar(
            'solicitud_datos_rechazada',
            'Rechazada solicitud de cambio de datos del hermano n.º '.$numeroHermano.' (solicitud #'.$solicitud->id.').'
        );
    }
}
