<?php

namespace App\Listeners;

use App\Support\Auditoria;
use Illuminate\Auth\Events\Failed;
use Illuminate\Support\Str;

class AuditarIntentoLoginFallido
{
    public function handle(Failed $event): void
    {
        $req = request();
        $cred = $event->credentials;
        $email = $cred['email'] ?? $cred['username'] ?? null;
        $email = is_string($email) ? Str::limit(trim($email), 255) : null;

        $esPortal = $event->guard === 'portal';

        Auditoria::registrar([
            'canal' => $esPortal ? 'portal_invitado' : 'admin',
            'evento' => 'login_fallo',
            'descripcion' => 'Intento de login fallido ('.($esPortal ? 'portal hermano' : 'administración').')'
                .($email ? ' · email: '.$email : ''),
            'email_intento' => $email,
            'payload' => Auditoria::truncarPayload([
                'guard' => $event->guard,
                'ip' => $req->ip(),
            ]),
        ], $req);
    }
}
