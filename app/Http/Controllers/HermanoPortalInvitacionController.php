<?php

namespace App\Http\Controllers;

use App\Models\Hermano;
use App\Models\HermanoPortalCuenta;
use App\Notifications\PortalInvitacionAccesoNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class HermanoPortalInvitacionController extends Controller
{
    public function store(Request $request, Hermano $hermano): RedirectResponse
    {
        if (blank($hermano->email)) {
            return redirect()
                ->route('hermanos.show', $hermano)
                ->with('error', 'El hermano no tiene email registrado. Añada un correo antes de activar el portal.');
        }

        $existente = HermanoPortalCuenta::query()->where('hermano_id', $hermano->id)->first();
        if ($existente && filled($existente->password)) {
            return redirect()
                ->route('hermanos.show', $hermano)
                ->with('error', 'Este hermano ya completó la activación del portal. Puede usar «Olvidé mi contraseña» en el acceso del portal si lo necesita.');
        }

        $token = Str::random(64);
        $horas = 72;
        $codigo = str_pad((string) random_int(0, 999_999), 6, '0', STR_PAD_LEFT);

        HermanoPortalCuenta::query()->updateOrCreate(
            ['hermano_id' => $hermano->id],
            [
                'email' => $hermano->email,
                'email_verified_at' => null,
                'activacion_token_hash' => hash('sha256', $token),
                'activacion_expira' => now()->addHours($horas),
                'activacion_codigo_hash' => hash('sha256', $codigo),
                'activacion_codigo_expira' => now()->addHours($horas),
                'password' => null,
                'recuperacion_codigo_hash' => null,
                'recuperacion_expira' => null,
            ]
        );

        $cuenta = HermanoPortalCuenta::query()->where('hermano_id', $hermano->id)->firstOrFail();
        $cuenta->notify(new PortalInvitacionAccesoNotification($token, $codigo, $horas));

        return redirect()
            ->route('hermanos.show', $hermano)
            ->with('status', 'Se ha enviado un correo con enlace seguro y código de 6 dígitos para activar el portal ('.$horas.' h de validez).');
    }
}
