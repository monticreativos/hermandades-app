<?php

namespace App\Http\Controllers\Economia;

use App\Http\Controllers\Controller;
use App\Http\Requests\Economia\StoreMovimientoRapidoRequest;
use App\Models\Hermano;
use App\Models\Proveedor;
use App\Services\Contabilidad\CategoriaMovimientoEconomia;
use App\Services\Contabilidad\MovimientoRapidoService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class MovimientoRapidoController extends Controller
{
    public function __construct(
        private readonly MovimientoRapidoService $movimientoRapidoService
    ) {}

    public function create(): View
    {
        $hermanos = Hermano::query()
            ->where('estado', 'Alta')
            ->orderBy('numero_hermano')
            ->get(['id', 'numero_hermano', 'nombre', 'apellidos']);

        $proveedores = Proveedor::query()
            ->orderBy('razon_social')
            ->limit(300)
            ->get(['id', 'razon_social', 'nif_cif']);

        $categoriasIngreso = [
            CategoriaMovimientoEconomia::IngresoCuota,
            CategoriaMovimientoEconomia::IngresoDonativo,
            CategoriaMovimientoEconomia::IngresoLoteria,
            CategoriaMovimientoEconomia::IngresoActividadEconomica,
        ];
        $categoriasGasto = [
            CategoriaMovimientoEconomia::GastoFlores,
            CategoriaMovimientoEconomia::GastoCera,
            CategoriaMovimientoEconomia::GastoCultos,
            CategoriaMovimientoEconomia::GastoCaridad,
        ];
        $categoriasLiquidacion = [
            CategoriaMovimientoEconomia::PagoProveedor,
        ];

        return view('economia.movimiento-rapido.create', [
            'hermanos' => $hermanos,
            'proveedores' => $proveedores,
            'categoriasIngreso' => $categoriasIngreso,
            'categoriasGasto' => $categoriasGasto,
            'categoriasLiquidacion' => $categoriasLiquidacion,
        ]);
    }

    public function store(StoreMovimientoRapidoRequest $request): RedirectResponse
    {
        $categoria = CategoriaMovimientoEconomia::from($request->validated('categoria'));

        try {
            $this->movimientoRapidoService->registrar([
                'categoria' => $categoria,
                'fecha' => Carbon::parse($request->validated('fecha')),
                'importe' => (float) $request->validated('importe'),
                'metodo_tesoreria' => $request->validated('metodo_tesoreria'),
                'hermano_id' => $request->filled('hermano_id') ? (int) $request->validated('hermano_id') : null,
                'apt_modelo_182' => $request->boolean('apt_modelo_182'),
                'glosa_personalizada' => $request->filled('glosa') ? trim((string) $request->validated('glosa')) : null,
                'proveedor_texto' => $request->filled('proveedor_texto') ? trim((string) $request->validated('proveedor_texto')) : null,
                'proveedor_id' => $request->filled('proveedor_id') ? (int) $request->validated('proveedor_id') : null,
                'base_imponible' => $request->filled('base_imponible') ? (float) $request->validated('base_imponible') : null,
                'cuota_iva' => $request->filled('cuota_iva') ? (float) $request->validated('cuota_iva') : null,
                'archivo' => $request->file('archivo'),
                'canal_origen' => 'manual_rapido',
            ]);
        } catch (\InvalidArgumentException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        } catch (\RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        } catch (\Throwable) {
            return back()->withInput()->with('error', 'No se pudo registrar el movimiento.');
        }

        return redirect()
            ->route('economia.libro-diario.index')
            ->with('status', 'Movimiento registrado. El asiento contable se ha generado automáticamente en el libro diario.');
    }
}
