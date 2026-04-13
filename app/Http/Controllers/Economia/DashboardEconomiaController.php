<?php

namespace App\Http\Controllers\Economia;

use App\Http\Controllers\Controller;
use App\Models\Apunte;
use App\Models\Ejercicio;
use App\Models\VentaTiendaLinea;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardEconomiaController extends Controller
{
    public function __invoke(Request $request): View
    {
        $ejercicio = (int) $request->get('ejercicio', (int) now()->format('Y'));
        $cacheKey = 'dashboard.economia.'.$ejercicio.'.'.now()->format('YmdHi').'.'.(int) floor(now()->minute / 10);

        $data = Cache::remember($cacheKey, now()->addMinutes(10), function () use ($ejercicio): array {
            $saldo570 = (float) Apunte::query()
                ->join('cuentas_contables', 'cuentas_contables.id', '=', 'apuntes.cuenta_contable_id')
                ->where('cuentas_contables.codigo', 'like', '570%')
                ->selectRaw('COALESCE(SUM(apuntes.debe - apuntes.haber),0) as s')
                ->value('s');
            $saldo572 = (float) Apunte::query()
                ->join('cuentas_contables', 'cuentas_contables.id', '=', 'apuntes.cuenta_contable_id')
                ->where('cuentas_contables.codigo', 'like', '572%')
                ->selectRaw('COALESCE(SUM(apuntes.debe - apuntes.haber),0) as s')
                ->value('s');

            $alCorriente = DB::table('hermanos')->where('estado_cuota', 'Al_corriente')->count();
            $pendientes = DB::table('hermanos')->where('estado_cuota', 'Pendiente')->count();
            $devueltos = DB::table('hermanos')->where('estado_cuota', 'Impagada')->count();

            $ventasSemana = (float) DB::table('ventas_tienda')
                ->whereDate('created_at', '>=', now()->subDays(7)->toDateString())
                ->sum('importe_total');
            $top = VentaTiendaLinea::query()
                ->join('ventas_tienda', 'ventas_tienda.id', '=', 'venta_tienda_lineas.venta_tienda_id')
                ->join('productos_tienda', 'productos_tienda.id', '=', 'venta_tienda_lineas.producto_tienda_id')
                ->whereDate('ventas_tienda.created_at', '>=', now()->subDays(7)->toDateString())
                ->selectRaw('productos_tienda.nombre, SUM(venta_tienda_lineas.cantidad) as uds')
                ->groupBy('productos_tienda.nombre')
                ->orderByDesc('uds')
                ->first();

            $ingresosMes = array_fill(0, 12, 0.0);
            $gastosMes = array_fill(0, 12, 0.0);

            $rows = Apunte::query()
                ->join('asientos', 'asientos.id', '=', 'apuntes.asiento_id')
                ->leftJoin('cuentas_contables', 'cuentas_contables.id', '=', 'apuntes.cuenta_contable_id')
                ->whereYear('asientos.fecha', $ejercicio)
                ->selectRaw('MONTH(asientos.fecha) as mes,
                    SUM(CASE WHEN (cuentas_contables.tipo = "Ingreso" OR cuentas_contables.codigo LIKE "7%") THEN apuntes.haber ELSE 0 END) as ingresos,
                    SUM(CASE WHEN (cuentas_contables.tipo = "Gasto" OR cuentas_contables.codigo LIKE "6%") THEN apuntes.debe ELSE 0 END) as gastos')
                ->groupBy('mes')
                ->get();
            foreach ($rows as $r) {
                $idx = max(1, min(12, (int) $r->mes)) - 1;
                $ingresosMes[$idx] = round((float) $r->ingresos, 2);
                $gastosMes[$idx] = round((float) $r->gastos, 2);
            }

            return [
                'saldo570' => round($saldo570, 2),
                'saldo572' => round($saldo572, 2),
                'cuotas' => [
                    'al_corriente' => $alCorriente,
                    'pendientes' => $pendientes,
                    'devueltos' => $devueltos,
                ],
                'ventasSemana' => round($ventasSemana, 2),
                'topVentas' => $top ? ['nombre' => $top->nombre, 'uds' => (int) $top->uds] : null,
                'labelsMeses' => ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'],
                'ingresosMes' => $ingresosMes,
                'gastosMes' => $gastosMes,
            ];
        });

        $ejercicios = Ejercicio::query()->orderByDesc('año')->pluck('año')->all();
        if ($ejercicios === []) {
            $ejercicios = [(int) now()->format('Y')];
        }

        return view('economia.dashboard', $data + [
            'ejercicioActual' => $ejercicio,
            'ejercicios' => $ejercicios,
        ]);
    }
}
