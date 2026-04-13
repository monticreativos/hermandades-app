<?php

namespace App\Http\Controllers\Economia;

use App\Http\Controllers\Controller;
use App\Models\CuentaContable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CuentaContableController extends Controller
{
    public function index(Request $request): View
    {
        $q = trim((string) $request->string('q'));
        $tipo = $request->string('tipo')->toString();
        $grupo = $request->string('grupo')->toString();

        $cuentas = CuentaContable::query()
            ->when($q !== '', fn ($query) => $query->where(function ($sub) use ($q): void {
                $sub->where('codigo', 'like', '%'.$q.'%')
                    ->orWhere('nombre', 'like', '%'.$q.'%');
            }))
            ->when(in_array($tipo, ['Activo', 'Pasivo', 'Patrimonio', 'Ingreso', 'Gasto'], true), fn ($query) => $query->where('tipo', $tipo))
            ->when(in_array($grupo, ['1', '2', '3', '4', '5', '6', '7'], true), fn ($query) => $query->where('codigo', 'like', $grupo.'%'))
            ->orderBy('codigo')
            ->get();

        $gruposNombres = [
            '1' => 'Financiación básica',
            '2' => 'Activo no corriente',
            '3' => 'Existencias',
            '4' => 'Acreedores y deudores',
            '5' => 'Cuentas financieras',
            '6' => 'Compras y gastos',
            '7' => 'Ventas e ingresos',
        ];

        $cuentasAgrupadas = $cuentas->groupBy(fn (CuentaContable $c) => substr($c->codigo, 0, 1));

        $totalesTipo = CuentaContable::query()
            ->selectRaw('tipo, count(*) as total')
            ->groupBy('tipo')
            ->orderBy('tipo')
            ->pluck('total', 'tipo');

        return view('economia.plan-contable.index', [
            'cuentasAgrupadas' => $cuentasAgrupadas,
            'gruposNombres' => $gruposNombres,
            'totalesTipo' => $totalesTipo,
            'totalCuentas' => CuentaContable::count(),
        ]);
    }

    public function search(Request $request): JsonResponse
    {
        $q = trim((string) $request->string('q'));

        $concatNombreCompleto = match (DB::connection()->getDriverName()) {
            'sqlite' => "TRIM(COALESCE(nombre, '') || ' ' || COALESCE(apellidos, ''))",
            default => "TRIM(CONCAT(COALESCE(nombre, ''), ' ', COALESCE(apellidos, '')))",
        };

        $cuentas = CuentaContable::query()
            ->when($q !== '', function ($query) use ($q, $concatNombreCompleto): void {
                $query->where(function ($sub) use ($q, $concatNombreCompleto): void {
                    $sub->where('codigo', 'like', '%'.$q.'%')
                        ->orWhere('nombre', 'like', '%'.$q.'%')
                        ->orWhereHas('hermanoVinculado', function ($h) use ($q, $concatNombreCompleto): void {
                            $h->where('nombre', 'like', '%'.$q.'%')
                                ->orWhere('apellidos', 'like', '%'.$q.'%')
                                ->orWhere('dni', 'like', '%'.$q.'%')
                                ->orWhereRaw($concatNombreCompleto.' LIKE ?', ['%'.$q.'%']);
                        })
                        ->orWhereHas('proveedorVinculado', function ($p) use ($q): void {
                            $p->where('razon_social', 'like', '%'.$q.'%')
                                ->orWhere('nombre_comercial', 'like', '%'.$q.'%')
                                ->orWhere('nif_cif', 'like', '%'.$q.'%');
                        });
                });
            })
            ->orderBy('codigo')
            ->limit(30)
            ->get(['id', 'codigo', 'nombre', 'tipo']);

        return response()->json([
            'cuentas' => $cuentas->map(fn (CuentaContable $c) => [
                'id' => $c->id,
                'codigo' => $c->codigo,
                'nombre' => $c->nombre,
                'tipo' => $c->tipo,
                'label' => $c->codigo.' — '.$c->nombre,
            ]),
        ]);
    }
}
