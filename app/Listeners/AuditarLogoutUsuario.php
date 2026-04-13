<?php

namespace App\Listeners;

use App\Models\HermanoPortalCuenta;
use App\Models\User;
use App\Support\Auditoria;
use Illuminate\Auth\Events\Logout;

class AuditarLogoutUsuario
{
    public function handle(Logout $event): void
    {
        $req = request();

        if ($event->guard === 'web' && $event->user instanceof User) {
            Auditoria::registrar([
                'canal' => 'admin',
                'evento' => 'logout',
                'descripcion' => 'Cierre de sesión (panel administración): '.$event->user->email,
                'user_id' => $event->user->id,
            ], $req);
        }

        if ($event->guard === 'portal' && $event->user instanceof HermanoPortalCuenta) {
            $c = $event->user;
            Auditoria::registrar([
                'canal' => 'portal',
                'evento' => 'logout',
                'descripcion' => 'Cierre de sesión (portal del hermano). Cuenta portal #'.$c->id,
                'hermano_portal_cuenta_id' => $c->id,
                'hermano_id' => $c->hermano_id,
            ], $req);
        }
    }
}
