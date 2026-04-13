<?php

namespace App\Listeners;

use App\Models\HermanoPortalCuenta;
use App\Models\User;
use App\Support\Auditoria;
use Illuminate\Auth\Events\Login;

class AuditarLoginExitoso
{
    public function handle(Login $event): void
    {
        $req = request();

        if ($event->guard === 'web' && $event->user instanceof User) {
            Auditoria::registrar([
                'canal' => 'admin',
                'evento' => 'login_ok',
                'descripcion' => 'Inicio de sesión correcto (panel administración): '.$event->user->email,
                'user_id' => $event->user->id,
                'payload' => Auditoria::truncarPayload([
                    'email' => $event->user->email,
                    'nombre' => $event->user->name,
                ]),
            ], $req);
        }

        if ($event->guard === 'portal' && $event->user instanceof HermanoPortalCuenta) {
            $c = $event->user;
            Auditoria::registrar([
                'canal' => 'portal',
                'evento' => 'login_ok',
                'descripcion' => 'Inicio de sesión correcto (portal del hermano). Cuenta portal #'.$c->id,
                'hermano_portal_cuenta_id' => $c->id,
                'hermano_id' => $c->hermano_id,
                'payload' => Auditoria::truncarPayload([
                    'email_cuenta' => $c->email,
                ]),
            ], $req);
        }
    }
}
