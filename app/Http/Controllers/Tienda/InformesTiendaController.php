<?php

namespace App\Http\Controllers\Tienda;

use App\Http\Controllers\Controller;
use App\Models\ProductoTienda;
use App\Models\VentaTiendaLinea;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class InformesTiendaController extends Controller
{
    public function index(): View
    {
        return view('tienda.informes.index');
    }

    public function ranking(Request $request): View
    {
        $desde = Carbon::parse($request->get('desde', now()->subMonths(3)->toDateString()))->startOfDay();
        $hasta = Carbon::parse($request->get('hasta', now()->toDateString()))->endOfDay();

        $rows = VentaTiendaLinea::query()
            ->join('ventas_tienda', 'ventas_tienda.id', '=', 'venta_tienda_lineas.venta_tienda_id')
            ->whereBetween('ventas_tienda.created_at', [$desde, $hasta])
            ->select([
                'venta_tienda_lineas.producto_tienda_id',
                DB::raw('SUM(venta_tienda_lineas.cantidad) as unidades'),
                DB::raw('SUM(venta_tienda_lineas.total_linea) as importe_ttc'),
            ])
            ->groupBy('venta_tienda_lineas.producto_tienda_id')
            ->orderByDesc('unidades')
            ->limit(50)
            ->get();

        $ids = $rows->pluck('producto_tienda_id');
        $nombres = ProductoTienda::query()->whereIn('id', $ids)->pluck('nombre', 'id');

        return view('tienda.informes.ranking', [
            'desde' => $desde->toDateString(),
            'hasta' => $hasta->toDateString(),
            'rows' => $rows,
            'nombres' => $nombres,
        ]);
    }

    public function margenes(Request $request): View
    {
        $desde = Carbon::parse($request->get('desde', now()->subMonths(3)->toDateString()))->startOfDay();
        $hasta = Carbon::parse($request->get('hasta', now()->toDateString()))->endOfDay();

        $lineas = VentaTiendaLinea::query()
            ->join('ventas_tienda', 'ventas_tienda.id', '=', 'venta_tienda_lineas.venta_tienda_id')
            ->whereBetween('ventas_tienda.created_at', [$desde, $hasta])
            ->select([
                'venta_tienda_lineas.base_imponible_linea',
                'venta_tienda_lineas.cantidad',
                'venta_tienda_lineas.precio_coste_unitario_snapshot',
            ])
            ->get();

        $ventasBase = round($lineas->sum(fn ($l) => (float) $l->base_imponible_linea), 2);
        $coste = round($lineas->sum(fn ($l) => (float) $l->precio_coste_unitario_snapshot * (int) $l->cantidad), 2);
        $margen = round($ventasBase - $coste, 2);

        return view('tienda.informes.margenes', [
            'desde' => $desde->toDateString(),
            'hasta' => $hasta->toDateString(),
            'ventasBase' => $ventasBase,
            'coste' => $coste,
            'margen' => $margen,
        ]);
    }

    public function stockBajo(): View
    {
        $productos = ProductoTienda::query()
            ->where('activo', true)
            ->whereColumn('stock_actual', '<=', 'stock_minimo')
            ->orderBy('stock_actual')
            ->orderBy('nombre')
            ->get();

        return view('tienda.informes.stock-bajo', [
            'productos' => $productos,
        ]);
    }
}
