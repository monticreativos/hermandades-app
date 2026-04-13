<?php

namespace App\Http\Middleware;

use App\Support\Auditoria;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class AuditarPeticionesHttp
{
    public function handle(Request $request, Closure $next): Response
    {
        return $next($request);
    }

    public function terminate(Request $request, Response $response): void
    {
        if ($this->debeIgnorar($request)) {
            return;
        }

        $path = $request->path();
        $method = $request->method();
        $code = $response->getStatusCode();

        if ($request->routeIs('logout') || $request->routeIs('portal.logout')) {
            return;
        }

        if ($path === 'login' && $method === 'POST') {
            return;
        }

        if ($path === 'portal/login' && $method === 'POST') {
            return;
        }

        $web = auth()->guard('web');
        $portal = auth()->guard('portal');

        if ($portal->check() && str_starts_with($path, 'portal')) {
            $payload = $this->payloadParaRequest($request);
            Auditoria::registrarDesdePeticion(
                $request,
                $code,
                'portal',
                'peticion_portal',
                $this->descPeticion($request, $response),
                $payload
            );

            return;
        }

        if ($web->check()) {
            if (in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
                $payload = $this->payloadParaRequest($request);
                Auditoria::registrarDesdePeticion(
                    $request,
                    $code,
                    'admin',
                    'peticion_admin_mutacion',
                    $this->descPeticion($request, $response),
                    $payload
                );

                return;
            }

            if ($method === 'GET' && $this->debeAuditarGetAdmin($path)) {
                Auditoria::registrarDesdePeticion(
                    $request,
                    $code,
                    'admin',
                    'peticion_admin_consulta',
                    $this->descPeticion($request, $response),
                    null
                );
            }

            return;
        }

        if (str_starts_with($path, 'portal/') && $method === 'POST' && $path !== 'portal/login') {
            $payload = $this->payloadParaRequest($request);
            Auditoria::registrar([
                'canal' => 'portal_invitado',
                'evento' => 'peticion_portal_invitado',
                'descripcion' => $this->descPeticion($request, $response),
                'codigo_http' => $code,
                'payload' => $payload !== null ? Auditoria::truncarPayload($payload) : null,
                'ip_address' => $request->ip(),
                'user_agent' => Str::limit((string) $request->userAgent(), 4000, '…'),
                'metodo_http' => $method,
                'ruta' => $request->route()?->getName(),
                'path' => '/'.$path,
            ], $request);

            return;
        }

        if (! $portal->check() && ! $web->check() && $method === 'GET' && str_starts_with($path, 'portal/')) {
            if (
                preg_match('#^portal/activar/#', $path)
                || $path === 'portal/activacion/codigo'
                || str_starts_with($path, 'portal/recuperar')
                || $path === 'portal/login'
                || str_starts_with($path, 'portal/verify-email')
            ) {
                $pathLog = preg_replace('#^(portal/activar/)[A-Za-z0-9]+$#', '$1[oculto]', $path);
                Auditoria::registrar([
                    'canal' => 'portal_invitado',
                    'evento' => 'vista_portal_invitado',
                    'descripcion' => 'Acceso GET a pantalla del portal (invitado) · '.$request->route()?->getName(),
                    'codigo_http' => $code,
                    'ip_address' => $request->ip(),
                    'user_agent' => Str::limit((string) $request->userAgent(), 4000, '…'),
                    'metodo_http' => $method,
                    'ruta' => $request->route()?->getName(),
                    'path' => '/'.$pathLog,
                ], $request);
            }
        }
    }

    private function debeIgnorar(Request $request): bool
    {
        $path = $request->path();

        return $path === 'up'
            || str_starts_with($path, 'telescope')
            || str_starts_with($path, 'horizon')
            || str_starts_with($path, 'livewire')
            || str_starts_with($path, 'sanctum')
            || str_starts_with($path, '_ignition');
    }

    private function debeAuditarGetAdmin(string $path): bool
    {
        $prefixes = [
            'hermanos', 'economia', 'salida', 'secretaria', 'patrimonio', 'informes',
            'ajustes', 'profile', 'search', 'dashboard', 'bancos', 'admin',
        ];

        foreach ($prefixes as $p) {
            if ($path === $p || str_starts_with($path, $p.'/')) {
                return true;
            }
        }

        return false;
    }

    private function descPeticion(Request $request, Response $response): string
    {
        $route = $request->route()?->getName() ?? '—';
        $ctrl = $request->route()?->getActionName() ?? '—';

        return sprintf(
            '%s %s · HTTP %d · ruta:%s · %s',
            $request->method(),
            '/'.$request->path(),
            $response->getStatusCode(),
            $route,
            $ctrl
        );
    }

    /**
     * @return array<string, mixed>|null
     */
    private function payloadParaRequest(Request $request): ?array
    {
        if (! in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            return null;
        }

        $data = $request->except(['_token']);

        foreach ($data as $k => $v) {
            if ($v instanceof UploadedFile) {
                $data[$k] = '[archivo: '.$v->getClientOriginalName().']';
            }
        }

        if ($data === []) {
            return null;
        }

        return Auditoria::sanitizarEntrada($data);
    }
}
