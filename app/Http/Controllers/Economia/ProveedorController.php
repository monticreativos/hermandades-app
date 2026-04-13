<?php

namespace App\Http\Controllers\Economia;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProveedorRequest;
use App\Http\Requests\UpdateProveedorRequest;
use App\Models\Apunte;
use App\Models\Proveedor;
use App\Services\Contabilidad\CuentaAuxiliarContableService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProveedorController extends Controller
{
    public function __construct(
        private readonly CuentaAuxiliarContableService $cuentaAuxiliarContableService
    ) {}

    public function buscar(Request $request): JsonResponse
    {
        $q = trim((string) $request->string('q'));

        $query = Proveedor::query()->orderBy('razon_social');

        if ($q !== '') {
            $query->where(function ($w) use ($q): void {
                $w->where('razon_social', 'like', '%'.$q.'%')
                    ->orWhere('nombre_comercial', 'like', '%'.$q.'%')
                    ->orWhere('nif_cif', 'like', '%'.$q.'%');
            });
        }

        $items = $query->limit(80)->get()->map(fn (Proveedor $p): array => [
            'value' => (string) $p->id,
            'text' => $p->etiquetaListado(),
        ]);

        return response()->json($items->values()->all());
    }

    public function show(Proveedor $proveedor): JsonResponse
    {
        return response()->json(['proveedor' => $proveedor]);
    }

    public function store(StoreProveedorRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['pais'] = strtoupper((string) ($data['pais'] ?? 'ES'));

        $proveedor = Proveedor::query()->create($data);
        $this->cuentaAuxiliarContableService->obtenerOCrearParaProveedor($proveedor->fresh());

        return response()->json([
            'ok' => true,
            'message' => 'Proveedor creado.',
            'option' => [
                'value' => (string) $proveedor->id,
                'text' => $proveedor->etiquetaListado(),
            ],
            'proveedor' => $proveedor,
        ], 201);
    }

    public function update(UpdateProveedorRequest $request, Proveedor $proveedor): JsonResponse
    {
        $data = $request->validated();
        if (array_key_exists('pais', $data) && $data['pais'] !== null) {
            $data['pais'] = strtoupper((string) $data['pais']);
        }

        $proveedor->update($data);
        $this->cuentaAuxiliarContableService->sincronizarEtiquetaCuentaProveedor($proveedor->fresh());

        return response()->json([
            'ok' => true,
            'message' => 'Proveedor actualizado.',
            'option' => [
                'value' => (string) $proveedor->id,
                'text' => $proveedor->fresh()->etiquetaListado(),
            ],
            'proveedor' => $proveedor->fresh(),
        ]);
    }

    public function destroy(Proveedor $proveedor): JsonResponse
    {
        $proveedor->delete();

        return response()->json(['ok' => true, 'message' => 'Proveedor eliminado.']);
    }

    public function extractoContable(Proveedor $proveedor): View
    {
        $proveedor->load('cuentaContable');
        $cuentaSel = $proveedor->cuentaContable;
        $movimientos = collect();
        if ($cuentaSel) {
            $movimientos = Apunte::extractoSubcuentaConSaldo($cuentaSel->id);
        }

        return view('economia.proveedores.extracto-contable', compact('proveedor', 'movimientos', 'cuentaSel'));
    }
}
