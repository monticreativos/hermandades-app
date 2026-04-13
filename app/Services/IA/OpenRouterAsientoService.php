<?php

namespace App\Services\IA;

use App\Models\CuentaContable;
use App\Models\Hermano;
use App\Models\Proveedor;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class OpenRouterAsientoService
{
    public function __construct(
        private readonly HttpFactory $http
    ) {}

    /**
     * @return array{
     *   glosa: string,
     *   lineas: array<int, array{cuenta_contable_id:int, cuenta_label:string, cuenta_tipo:string, debe:float, haber:float, concepto_detalle:string}>
     * }
     */
    public function generarAsientoDesdeDescripcion(string $descripcion, string $tratamientoIva = 'auto'): array
    {
        $apiKey = (string) config('services.openrouter.api_key');
        if ($apiKey === '') {
            throw new \RuntimeException('OpenRouter no está configurado. Defina OPENROUTER_API_KEY en el entorno.');
        }

        $cuentas = CuentaContable::query()
            ->orderBy('codigo')
            ->get(['id', 'codigo', 'nombre', 'tipo', 'proveedor_id']);

        $esContextoGasto = $this->esContextoGasto($descripcion);
        $ivaEsperado = $this->clasificarIvaEsperado($descripcion, $esContextoGasto, $tratamientoIva);

        $cuentasPrompt = $this->seleccionarCuentasParaPrompt($cuentas, $esContextoGasto);
        $planSimplificado = $cuentasPrompt
            ->map(fn (CuentaContable $c) => $c->codigo.' | '.$c->nombre.' | '.$c->tipo)
            ->values()
            ->all();

        $hermanosConCuenta = Hermano::query()
            ->with('cuentaContable:id,codigo,nombre,tipo')
            ->whereNotNull('cuenta_contable_id')
            ->orderBy('numero_hermano')
            ->limit(200)
            ->get(['id', 'numero_hermano', 'nombre', 'apellidos', 'cuenta_contable_id'])
            ->values();
        $proveedoresConCuenta = Proveedor::query()
            ->with('cuentaContable:id,codigo,nombre,tipo')
            ->whereNotNull('cuenta_contable_id')
            ->orderBy('razon_social')
            ->limit(200)
            ->get(['id', 'razon_social', 'nif_cif', 'cuenta_contable_id'])
            ->values();

        $hermanosMencionados = $this->filtrarHermanosMencionados($descripcion, $hermanosConCuenta);
        $proveedoresMencionados = $this->filtrarProveedoresMencionados($descripcion, $proveedoresConCuenta);

        $auxHermanos = $hermanosMencionados
            ->take(20)
            ->map(function (Hermano $h): string {
                $nombre = trim($h->nombre.' '.$h->apellidos);
                $codigo = $h->cuentaContable?->codigo ?? 'sin-cuenta';

                return 'Hermano: '.$nombre.' | Nº '.$h->numero_hermano.' | subcuenta '.$codigo;
            })
            ->values()
            ->all();
        $auxProveedores = $proveedoresMencionados
            ->take(30)
            ->map(function (Proveedor $p): string {
                $codigo = $p->cuentaContable?->codigo ?? 'sin-cuenta';
                $nif = $p->nif_cif ? ' | '.$p->nif_cif : '';

                return 'Proveedor: '.$p->razon_social.$nif.' | subcuenta '.$codigo;
            })
            ->values()
            ->all();

        $promptSistema = implode("\n", [
            'Eres un contable experto en Hermandades de España (PGC ES).',
            'Responde SOLO con JSON válido sin texto adicional.',
            'Debes devolver este esquema exacto:',
            '{"glosa":"string","lineas":[{"cuenta_codigo":"string","debe":0,"haber":0,"concepto":"string"}]}',
            'Reglas:',
            '- El asiento debe cuadrar exactamente (suma Debe = suma Haber).',
            '- Cada línea debe tener solo Debe o Haber (no ambos).',
            '- Usa cuentas del plan contable aportado.',
            '- Si se menciona un Hermano o Proveedor, prioriza su subcuenta auxiliar 430/410.',
            '- En gastos NUNCA uses cuentas 430 (clientes). Para compras/gastos con tercero usa 410 del proveedor si existe.',
            '- "Pago a proveedor" suele ser Debe 410 / Haber 57X.',
            '- "Compra o gasto" suele ser Debe 6XX (+472 si procede) / Haber 57X o 410 según descripción.',
            '- Si el texto indica IVA aplicable en gasto, incluye 472 al Debe.',
            '- Si el texto indica IVA repercutido en ingreso/venta, incluye 477 al Haber.',
            '- Si el texto indica "sin IVA", "exento" o "no sujeto", no incluyas 472/477.',
            '- En compras/gastos habituales de bienes o servicios (p. ej. flores, cera, mantenimiento), si no se indica exención, asume IVA y desglosa 472.',
            '- Si solo se da importe total con IVA, calcula base y cuota de forma coherente.',
            '- Importes con punto decimal.',
        ]);

        $promptUsuario = implode("\n\n", [
            'Descripción del usuario:',
            $descripcion,
            'Contexto detectado por el sistema: '.($esContextoGasto ? 'GASTO/PAGO' : 'INGRESO/OTRO'),
            'Modo IVA seleccionado por el usuario: '.$tratamientoIva,
            'Tratamiento IVA esperado por el sistema: '.$ivaEsperado,
            'Plan contable simplificado (codigo | nombre | tipo):',
            implode("\n", $planSimplificado),
            'Subcuentas auxiliares de Hermanos disponibles:',
            $auxHermanos === [] ? '(sin coincidencias claras en la descripción)' : implode("\n", $auxHermanos),
            'Subcuentas auxiliares de Proveedores disponibles:',
            $auxProveedores === [] ? '(sin coincidencias claras en la descripción)' : implode("\n", $auxProveedores),
        ]);

        $resp = $this->http
            ->withHeaders([
                'Authorization' => 'Bearer '.$apiKey,
                'Content-Type' => 'application/json',
            ])
            ->timeout(35)
            ->post('https://openrouter.ai/api/v1/chat/completions', [
                'model' => 'openrouter/auto',
                'temperature' => 0.1,
                'response_format' => ['type' => 'json_object'],
                'messages' => [
                    ['role' => 'system', 'content' => $promptSistema],
                    ['role' => 'user', 'content' => $promptUsuario],
                ],
            ]);

        if (! $resp->ok()) {
            throw new \RuntimeException('OpenRouter devolvió un error al generar el asiento.');
        }

        $body = $resp->json();
        $content = (string) Arr::get($body, 'choices.0.message.content', '');
        if ($content === '') {
            throw new \RuntimeException('La IA no devolvió contenido utilizable.');
        }

        /** @var array<string,mixed>|null $json */
        $json = json_decode($content, true);
        if (! is_array($json)) {
            throw new \RuntimeException('La IA devolvió una respuesta no válida.');
        }

        $lineasRaw = Arr::get($json, 'lineas', []);
        if (! is_array($lineasRaw) || $lineasRaw === []) {
            throw new \RuntimeException('No se recibieron líneas de asiento.');
        }

        $lineas = [];
        foreach ($lineasRaw as $linea) {
            if (! is_array($linea)) {
                continue;
            }
            $codigo = trim((string) ($linea['cuenta_codigo'] ?? $linea['cuenta'] ?? ''));
            $cuenta = $this->resolverCuenta($cuentas, $codigo);
            if (! $cuenta) {
                continue;
            }
            $debe = round((float) ($linea['debe'] ?? 0), 2);
            $haber = round((float) ($linea['haber'] ?? 0), 2);
            $concepto = trim((string) ($linea['concepto'] ?? ''));
            if ($debe <= 0 && $haber <= 0) {
                continue;
            }
            if ($debe > 0 && $haber > 0) {
                continue;
            }

            $lineas[] = [
                'cuenta_contable_id' => $cuenta->id,
                'cuenta_codigo' => (string) $cuenta->codigo,
                'cuenta_label' => $cuenta->codigo.' — '.$cuenta->nombre,
                'cuenta_tipo' => (string) $cuenta->tipo,
                'debe' => $debe,
                'haber' => $haber,
                'concepto_detalle' => $concepto,
            ];
        }

        if ($lineas === []) {
            throw new \RuntimeException('No se pudieron mapear cuentas válidas del plan contable.');
        }

        $sumDebe = round(collect($lineas)->sum('debe'), 2);
        $sumHaber = round(collect($lineas)->sum('haber'), 2);
        if (abs($sumDebe - $sumHaber) > 0.01) {
            throw new \RuntimeException('La propuesta IA no cuadra (Debe/Haber). Revise la descripción e inténtelo de nuevo.');
        }

        $lineas = $this->corregirAuxiliaresIncompatibles($lineas, $esContextoGasto, $proveedoresMencionados, $cuentas);
        $this->validarTratamientoIva($lineas, $ivaEsperado, $esContextoGasto);

        $glosa = trim((string) ($json['glosa'] ?? ''));
        if ($glosa === '') {
            $glosa = Str::limit($descripcion, 150, '');
        }

        return [
            'glosa' => $glosa,
            'lineas' => array_map(static function (array $l): array {
                unset($l['cuenta_codigo']);

                return $l;
            }, $lineas),
        ];
    }

    /**
     * @param  \Illuminate\Support\Collection<int,CuentaContable>  $cuentas
     */
    private function resolverCuenta($cuentas, string $codigoHint): ?CuentaContable
    {
        if ($codigoHint === '') {
            return null;
        }

        $codigo = trim((string) preg_replace('/[^0-9\.]/', '', $codigoHint));
        if ($codigo !== '') {
            $exacta = $cuentas->firstWhere('codigo', $codigo);
            if ($exacta instanceof CuentaContable) {
                return $exacta;
            }
            $comienza = $cuentas->first(fn (CuentaContable $c) => Str::startsWith($c->codigo, $codigo));
            if ($comienza instanceof CuentaContable) {
                return $comienza;
            }
        }

        $txt = Str::lower($codigoHint);

        return $cuentas->first(function (CuentaContable $c) use ($txt): bool {
            return Str::contains(Str::lower($c->nombre), $txt);
        });
    }

    /**
     * @param  Collection<int,CuentaContable>  $cuentas
     * @return Collection<int,CuentaContable>
     */
    private function seleccionarCuentasParaPrompt(Collection $cuentas, bool $esGasto): Collection
    {
        if ($esGasto) {
            $filtradas = $cuentas->filter(fn (CuentaContable $c) => Str::startsWith($c->codigo, ['6', '57', '410', '472']));

            return $filtradas->take(220);
        }

        $filtradas = $cuentas->filter(fn (CuentaContable $c) => Str::startsWith($c->codigo, ['7', '57', '430', '477']));

        return $filtradas->take(220);
    }

    private function esContextoGasto(string $descripcion): bool
    {
        $t = Str::lower($descripcion);
        $keywords = ['pago', 'factura', 'compra', 'flores', 'cera', 'culto', 'proveedor', 'restauracion', 'mantenimiento', 'gasto'];
        foreach ($keywords as $k) {
            if (Str::contains($t, $k)) {
                return true;
            }
        }

        return false;
    }

    private function clasificarIvaEsperado(string $descripcion, bool $esContextoGasto, string $tratamientoIva): string
    {
        if ($tratamientoIva === 'sin_iva') {
            return 'sin_iva';
        }
        if ($tratamientoIva === 'soportado') {
            return 'con_iva_gasto';
        }
        if ($tratamientoIva === 'repercutido') {
            return 'con_iva_ingreso';
        }

        $t = Str::lower($descripcion);

        $sinIva = ['sin iva', 'exento', 'exenta', 'no sujeto', 'no sujeta', 'sin impuesto'];
        foreach ($sinIva as $k) {
            if (Str::contains($t, $k)) {
                return 'sin_iva';
            }
        }

        $marcaIva = [' iva', 'iva ', 'al 21', 'al 10', 'al 4', '21%', '10%', '4%'];
        foreach ($marcaIva as $k) {
            if (Str::contains($t, $k)) {
                return $esContextoGasto ? 'con_iva_gasto' : 'con_iva_ingreso';
            }
        }

        // En gastos de compra/servicio, por defecto esperamos IVA salvo que el usuario indique exención.
        if ($esContextoGasto) {
            return 'con_iva_gasto';
        }

        return 'indefinido';
    }

    /**
     * @param  Collection<int,Hermano>  $hermanos
     * @return Collection<int,Hermano>
     */
    private function filtrarHermanosMencionados(string $descripcion, Collection $hermanos): Collection
    {
        $t = Str::lower($descripcion);

        return $hermanos->filter(function (Hermano $h) use ($t): bool {
            $nombre = Str::lower(trim($h->nombre.' '.$h->apellidos));
            $numero = (string) $h->numero_hermano;

            return Str::contains($t, $nombre) || Str::contains($t, 'nº '.$numero) || Str::contains($t, 'n '.$numero);
        })->values();
    }

    /**
     * @param  Collection<int,Proveedor>  $proveedores
     * @return Collection<int,Proveedor>
     */
    private function filtrarProveedoresMencionados(string $descripcion, Collection $proveedores): Collection
    {
        $t = Str::lower($descripcion);

        return $proveedores->filter(function (Proveedor $p) use ($t): bool {
            $razon = Str::lower((string) $p->razon_social);
            $nif = Str::lower((string) ($p->nif_cif ?? ''));

            return ($razon !== '' && Str::contains($t, $razon)) || ($nif !== '' && Str::contains($t, $nif));
        })->values();
    }

    /**
     * @param  array<int, array{cuenta_contable_id:int, cuenta_codigo:string, cuenta_label:string, cuenta_tipo:string, debe:float, haber:float, concepto_detalle:string}>  $lineas
     * @param  Collection<int,Proveedor>  $proveedoresMencionados
     * @param  Collection<int,CuentaContable>  $cuentas
     * @return array<int, array{cuenta_contable_id:int, cuenta_codigo:string, cuenta_label:string, cuenta_tipo:string, debe:float, haber:float, concepto_detalle:string}>
     */
    private function corregirAuxiliaresIncompatibles(array $lineas, bool $esContextoGasto, Collection $proveedoresMencionados, Collection $cuentas): array
    {
        if (! $esContextoGasto) {
            return $lineas;
        }

        $proveedorCuenta = $proveedoresMencionados->first()?->cuentaContable;
        $cuentaFallback410 = $cuentas->first(fn (CuentaContable $c) => Str::startsWith($c->codigo, '410') && $c->proveedor_id !== null);

        foreach ($lineas as $idx => $linea) {
            if (! Str::startsWith($linea['cuenta_codigo'], '430')) {
                continue;
            }

            if ($proveedorCuenta instanceof CuentaContable) {
                $lineas[$idx]['cuenta_contable_id'] = $proveedorCuenta->id;
                $lineas[$idx]['cuenta_codigo'] = (string) $proveedorCuenta->codigo;
                $lineas[$idx]['cuenta_label'] = $proveedorCuenta->codigo.' — '.$proveedorCuenta->nombre;
                $lineas[$idx]['cuenta_tipo'] = (string) $proveedorCuenta->tipo;

                continue;
            }

            if ($cuentaFallback410 instanceof CuentaContable) {
                $lineas[$idx]['cuenta_contable_id'] = $cuentaFallback410->id;
                $lineas[$idx]['cuenta_codigo'] = (string) $cuentaFallback410->codigo;
                $lineas[$idx]['cuenta_label'] = $cuentaFallback410->codigo.' — '.$cuentaFallback410->nombre;
                $lineas[$idx]['cuenta_tipo'] = (string) $cuentaFallback410->tipo;

                continue;
            }

            throw new \RuntimeException('La propuesta IA intentó usar cuenta 430 en un gasto. Registre/seleccione el proveedor para usar su subcuenta 410.');
        }

        return $lineas;
    }

    /**
     * @param  array<int, array{cuenta_contable_id:int, cuenta_codigo:string, cuenta_label:string, cuenta_tipo:string, debe:float, haber:float, concepto_detalle:string}>  $lineas
     */
    private function validarTratamientoIva(array $lineas, string $ivaEsperado, bool $esContextoGasto): void
    {
        $hay472Debe = collect($lineas)->contains(fn (array $l) => Str::startsWith($l['cuenta_codigo'], '472') && $l['debe'] > 0);
        $hay477Haber = collect($lineas)->contains(fn (array $l) => Str::startsWith($l['cuenta_codigo'], '477') && $l['haber'] > 0);
        $hayIva = $hay472Debe || $hay477Haber;

        if ($ivaEsperado === 'sin_iva' && $hayIva) {
            throw new \RuntimeException('El texto indica operación sin IVA, pero la propuesta incluye cuentas 472/477.');
        }

        if ($ivaEsperado === 'con_iva_gasto' && ! $hay472Debe) {
            throw new \RuntimeException('En este gasto falta IVA soportado (472). Si la operación está exenta/no sujeta, indíquelo explícitamente en la descripción ("sin IVA" o "exento").');
        }

        if ($ivaEsperado === 'con_iva_ingreso' && ! $hay477Haber) {
            throw new \RuntimeException('Se espera IVA repercutido en ingreso y falta la cuenta 477 al Haber. Indique base e IVA en la descripción.');
        }

        if ($ivaEsperado === 'indefinido') {
            // Guardarrail suave: evita 477 en gasto y 472 en ingreso salvo que el texto lo pida explícitamente.
            if ($esContextoGasto && $hay477Haber) {
                throw new \RuntimeException('En un gasto normal no debe aparecer 477 (IVA repercutido). Revise la descripción.');
            }
            if (! $esContextoGasto && $hay472Debe) {
                throw new \RuntimeException('En un ingreso normal no debe aparecer 472 (IVA soportado). Revise la descripción.');
            }
        }
    }
}
