<?php

namespace App\Http\Controllers;

use App\Models\Apunte;
use App\Models\Asiento;
use App\Models\ConfiguracionSalida;
use App\Models\EnsayoCuadrilla;
use App\Models\Hermano;
use App\Models\RemesaRecibo;
use App\Models\SolicitudCambioDatos;
use App\Models\VentaTienda;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $user = request()->user();
        $esGestion = $user?->hasAnyRole(['SuperAdmin', 'Administrador Hermandad', 'Secretaría', 'Mayordomía']) || $user?->hasPermissionTo('contabilidad.gestion');

        if (! $esGestion) {
            return $this->dashboardHermano();
        }

        $payload = Cache::remember('dashboard.gestion.'.now()->format('YmdHi').'.'.(int) floor(now()->minute / 10), now()->addMinutes(10), function (): array {
            $now = now();
            $inicioAnio = $now->copy()->startOfYear();
            $inicioMes = $now->copy()->startOfMonth();

            $totalHermanos = Hermano::query()->count();
            $altasAnio = Hermano::query()->whereDate('fecha_alta', '>=', $inicioAnio)->count();
            $bajasAnio = Hermano::query()->whereDate('fecha_baja', '>=', $inicioAnio)->count();

            $saldoCaja570 = (float) Apunte::query()
                ->join('cuentas_contables', 'cuentas_contables.id', '=', 'apuntes.cuenta_contable_id')
                ->where('cuentas_contables.codigo', 'like', '570%')
                ->selectRaw('COALESCE(SUM(apuntes.debe - apuntes.haber), 0) as saldo')
                ->value('saldo');
            $saldoBanco572 = (float) Apunte::query()
                ->join('cuentas_contables', 'cuentas_contables.id', '=', 'apuntes.cuenta_contable_id')
                ->where('cuentas_contables.codigo', 'like', '572%')
                ->selectRaw('COALESCE(SUM(apuntes.debe - apuntes.haber), 0) as saldo')
                ->value('saldo');
            $ventasMes = (float) VentaTienda::query()
                ->whereDate('created_at', '>=', $inicioMes->toDateString())
                ->sum('importe_total');
            $recibosDevueltosPendientes = RemesaRecibo::query()
                ->where('estado', RemesaRecibo::ESTADO_DEVUELTO)
                ->count();

            $labels = [];
            $seriesIngresos = [];
            $seriesGastos = [];
            for ($i = 5; $i >= 0; $i--) {
                $m = Carbon::now()->subMonths($i);
                $labels[] = $m->translatedFormat('M y');
                $row = Apunte::query()
                    ->join('asientos', 'asientos.id', '=', 'apuntes.asiento_id')
                    ->leftJoin('cuentas_contables', 'cuentas_contables.id', '=', 'apuntes.cuenta_contable_id')
                    ->whereYear('asientos.fecha', $m->year)
                    ->whereMonth('asientos.fecha', $m->month)
                    ->selectRaw('
                        COALESCE(SUM(CASE WHEN (cuentas_contables.tipo = "Ingreso" OR cuentas_contables.codigo LIKE "7%") THEN apuntes.haber ELSE 0 END), 0) as ingresos,
                        COALESCE(SUM(CASE WHEN (cuentas_contables.tipo = "Gasto" OR cuentas_contables.codigo LIKE "6%") THEN apuntes.debe ELSE 0 END), 0) as gastos
                    ')
                    ->first();
                $seriesIngresos[] = round((float) ($row->ingresos ?? 0), 2);
                $seriesGastos[] = round((float) ($row->gastos ?? 0), 2);
            }

            $ultimosAsientos = Asiento::query()
                ->with('ejercicio')
                ->latest('fecha')
                ->latest('numero_asiento')
                ->limit(5)
                ->get();
            $ultimasAltas = Hermano::query()
                ->whereNotNull('fecha_alta')
                ->orderByDesc('fecha_alta')
                ->limit(5)
                ->get(['id', 'numero_hermano', 'nombre', 'apellidos', 'fecha_alta']);

            $eventos = collect();
            $salida = ConfiguracionSalida::query()
                ->whereNotNull('fecha_salida')
                ->whereDate('fecha_salida', '>=', $now->toDateString())
                ->orderBy('fecha_salida')
                ->first();
            if ($salida) {
                $eventos->push([
                    'titulo' => 'Estación de Penitencia '.$salida->año,
                    'fecha' => $salida->fecha_salida,
                    'meta' => 'Salida principal',
                ]);
            }
            $ensayo = EnsayoCuadrilla::query()
                ->with('cuadrilla')
                ->whereDate('fecha', '>=', $now->toDateString())
                ->orderBy('fecha')
                ->first();
            if ($ensayo) {
                $eventos->push([
                    'titulo' => 'Ensayo '.($ensayo->cuadrilla->nombre ?? 'Cuadrilla'),
                    'fecha' => $ensayo->fecha,
                    'meta' => trim(($ensayo->hora_inicio ? $ensayo->hora_inicio.' · ' : '').($ensayo->lugar ?? '')),
                ]);
            }

            $solicitudesPendientes = SolicitudCambioDatos::query()
                ->where('estado', SolicitudCambioDatos::ESTADO_PENDIENTE)
                ->count();

            return [
                'totalHermanos' => $totalHermanos,
                'altasAnio' => $altasAnio,
                'bajasAnio' => $bajasAnio,
                'saldoCaja570' => round($saldoCaja570, 2),
                'saldoBanco572' => round($saldoBanco572, 2),
                'ventasMes' => round($ventasMes, 2),
                'recibosDevueltosPendientes' => $recibosDevueltosPendientes,
                'eventos' => $eventos,
                'chartLabels' => $labels,
                'chartIngresos' => $seriesIngresos,
                'chartGastos' => $seriesGastos,
                'solicitudesPendientes' => $solicitudesPendientes,
                'ultimosAsientos' => $ultimosAsientos,
                'ultimasAltas' => $ultimasAltas,
            ];
        });

        return view('dashboard', $payload + ['esDashboardGestion' => true]);
    }

    private function dashboardHermano(): View
    {
        $user = request()->user();
        $hermano = Hermano::query()
            ->where(function ($q) use ($user): void {
                $q->where('email', $user?->email)
                    ->orWhere(function ($qq) use ($user): void {
                        $qq->where('nombre', 'like', ($user?->name ?? '').'%');
                    });
            })
            ->orderByDesc('fecha_alta')
            ->first();

        if (! $hermano) {
            return view('dashboard', [
                'esDashboardGestion' => false,
                'hermano' => null,
                'proximoEvento' => null,
            ]);
        }

        $campaña = ConfiguracionSalida::query()
            ->whereNotNull('fecha_salida')
            ->whereDate('fecha_salida', '>=', now()->toDateString())
            ->orderBy('fecha_salida')
            ->first();

        return view('dashboard', [
            'esDashboardGestion' => false,
            'hermano' => $hermano,
            'antiguedad' => $hermano->fecha_alta ? (int) $hermano->fecha_alta->diffInYears(now()) : 0,
            'cuotaOk' => ! in_array($hermano->estado_cuota, ['Pendiente', 'Impagada'], true),
            'proximoEvento' => $campaña ? [
                'titulo' => 'Estación de Penitencia '.$campaña->año,
                'fecha' => $campaña->fecha_salida?->format('d/m/Y'),
            ] : null,
        ]);
    }
}
