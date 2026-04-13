<?php

namespace App\Support;

use App\Models\AuditoriaLog;
use App\Models\HermanoPortalCuenta;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class Auditoria
{
    private const CLAVES_SENSIBLES = [
        'password',
        'password_confirmation',
        'current_password',
        'token',
        '_token',
        'activacion_token_hash',
        'recuperacion_codigo_hash',
        'recuperacion_codigo',
        'codigo',
    ];

    /**
     * Registro genérico (usar desde listeners, jobs, etc.).
     *
     * @param  array<string, mixed>  $atributos
     */
    public static function registrar(array $atributos, ?Request $request = null): void
    {
        $req = $request ?? request();

        try {
            $base = [
                'ip_address' => $req->ip(),
                'user_agent' => Str::limit((string) $req->userAgent(), 4000, '…'),
                'metodo_http' => $req->method(),
                'ruta' => $req->route()?->getName(),
                'path' => '/'.$req->path(),
            ];

            AuditoriaLog::query()->create(array_merge($base, $atributos));
        } catch (\Throwable) {
            // No bloquear la petición por fallos de auditoría.
        }
    }

    /**
     * @param  array<string, mixed>|null  $payload
     */
    public static function registrarDesdePeticion(
        Request $request,
        ?int $codigoHttp,
        string $canal,
        string $evento,
        ?string $descripcion = null,
        ?array $payload = null
    ): void {
        $user = Auth::guard('web')->user();
        $portal = Auth::guard('portal')->user();

        self::registrar([
            'canal' => $canal,
            'evento' => $evento,
            'descripcion' => $descripcion,
            'user_id' => $user instanceof User ? $user->id : null,
            'hermano_portal_cuenta_id' => $portal instanceof HermanoPortalCuenta ? $portal->id : null,
            'hermano_id' => $portal instanceof HermanoPortalCuenta ? $portal->hermano_id : null,
            'codigo_http' => $codigoHttp,
            'payload' => $payload !== null ? self::truncarPayload($payload) : null,
        ], $request);
    }

    /**
     * @param  array<string, mixed>  $datos
     * @return array<string, mixed>
     */
    public static function sanitizarEntrada(array $datos): array
    {
        $limpio = Arr::except($datos, self::CLAVES_SENSIBLES);

        foreach ($limpio as $k => $v) {
            if (is_string($k) && Str::contains(strtolower($k), 'password')) {
                unset($limpio[$k]);
            }
        }

        return $limpio;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public static function truncarPayload(array $payload): array
    {
        $json = json_encode($payload, JSON_UNESCAPED_UNICODE);
        if ($json === false) {
            return ['_error' => 'payload_no_serializable'];
        }
        if (strlen($json) > 16000) {
            return ['_truncado' => true, 'vista' => Str::limit($json, 15000, '…')];
        }

        return $payload;
    }
}
