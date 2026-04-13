<?php

namespace App\Support;

use App\Models\Actividad;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class RegistroActividad
{
    public static function registrar(string $accion, string $descripcion): void
    {
        try {
            $descripcion = Str::limit(trim($descripcion), 495, '…');
            $accionCorta = Str::limit($accion, 78, '');
            Actividad::query()->create([
                'user_id' => Auth::id(),
                'accion' => $accionCorta,
                'descripcion' => $descripcion,
            ]);

            if (Auth::guard('web')->check()) {
                Auditoria::registrar([
                    'canal' => 'admin',
                    'evento' => 'actividad_negocio',
                    'descripcion' => $descripcion,
                    'user_id' => Auth::id(),
                    'payload' => Auditoria::truncarPayload(['accion' => $accionCorta]),
                ]);
            }
        } catch (\Throwable) {
            // No interrumpir el flujo principal si falla el log.
        }
    }
}
