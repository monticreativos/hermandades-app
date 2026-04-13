<?php

namespace App\Http\Controllers\Economia;

use App\Http\Controllers\Controller;
use App\Models\Asiento;
use App\Models\CuentaContable;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LibroDiarioController extends Controller
{
    public function index(Request $request): View
    {
        $cuentaId = $request->filled('cuenta_contable_id') ? (int) $request->input('cuenta_contable_id') : null;
        $desde = $this->normalizarFecha((string) $request->input('fecha_desde', ''));
        $hasta = $this->normalizarFecha((string) $request->input('fecha_hasta', ''));
        $q = trim((string) $request->string('q'));

        $asientos = Asiento::query()
            ->with(['ejercicio', 'apuntes.cuentaContable', 'documentosGasto'])
            ->when($desde, fn ($query) => $query->whereDate('fecha', '>=', $desde))
            ->when($hasta, fn ($query) => $query->whereDate('fecha', '<=', $hasta))
            ->when($cuentaId, function ($query) use ($cuentaId): void {
                $query->whereHas('apuntes', fn ($a) => $a->where('cuenta_contable_id', $cuentaId));
            })
            ->when($q !== '', function ($query) use ($q): void {
                $query->where(function ($sub) use ($q): void {
                    $sub->where('glosa', 'like', '%'.$q.'%');
                    if (ctype_digit($q)) {
                        $sub->orWhere('numero_asiento', (int) $q);
                    }
                });
            })
            ->orderByDesc('fecha')
            ->orderByDesc('numero_asiento')
            ->paginate(15)
            ->withQueryString();

        $cuentas = CuentaContable::query()->orderBy('codigo')->get(['id', 'codigo', 'nombre', 'tipo']);

        $cuentasJson = $cuentas->map(fn ($c) => [
            'id' => $c->id,
            'codigo' => $c->codigo,
            'nombre' => $c->nombre,
            'tipo' => $c->tipo,
            'label' => $c->codigo.' — '.$c->nombre,
        ])->values()->all();

        $asientosPayload = $asientos->getCollection()->map(function (Asiento $a) {
            $docs = $a->documentosGasto->keyBy('orden_linea');

            return [
                'id' => $a->id,
                'fecha' => $a->fecha->format('Y-m-d'),
                'glosa' => $a->glosa,
                'ejercicio_abierto' => $a->ejercicio->estaAbierto(),
                'apuntes' => $a->apuntes->values()->map(function ($p, $idx) use ($docs) {
                    $d = $docs->get($idx);

                    return [
                        'cuenta_contable_id' => $p->cuenta_contable_id,
                        'cuenta_tipo' => $p->cuentaContable->tipo,
                        'cuenta_label' => $p->cuentaContable->codigo.' — '.$p->cuentaContable->nombre,
                        'debe' => (float) $p->debe,
                        'haber' => (float) $p->haber,
                        'concepto_detalle' => $p->concepto_detalle ?? '',
                        'factura_proveedor' => $d?->proveedor ?? '',
                        'factura_estado' => $d?->estado ?? 'Pendiente',
                        'tiene_documento' => (bool) $d,
                    ];
                })->all(),
            ];
        })->values()->all();

        $asientoModalConfig = [
            'cuentasSearchUrl' => route('economia.cuentas.search'),
            'asientosPayload' => $asientosPayload,
            'createUrl' => route('economia.asientos.store'),
            'asientosBaseUrl' => url('/economia/asientos'),
            'aiGenerateUrl' => route('economia.asientos.ia-generar'),
        ];

        return view('economia.libro-diario.index', [
            'asientos' => $asientos,
            'cuentas' => $cuentas,
            'cuentasJson' => $cuentasJson,
            'asientosPayload' => $asientosPayload,
            'asientoModalConfig' => $asientoModalConfig,
        ]);
    }

    private function normalizarFecha(string $valor): ?string
    {
        $valor = trim($valor);
        if ($valor === '') {
            return null;
        }

        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $valor) === 1) {
            return $valor;
        }

        if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $valor) === 1) {
            [$d, $m, $y] = explode('/', $valor);

            return $y.'-'.$m.'-'.$d;
        }

        return null;
    }
}
