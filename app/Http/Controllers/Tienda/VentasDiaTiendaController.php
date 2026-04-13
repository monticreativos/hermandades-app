<?php

namespace App\Http\Controllers\Tienda;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\VentaTienda;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VentasDiaTiendaController extends Controller
{
    public function index(Request $request): View
    {
        $fecha = Carbon::parse($request->get('fecha', now()->toDateString()))->startOfDay();

        $ventas = VentaTienda::query()
            ->whereDate('created_at', $fecha)
            ->with(['user', 'hermano.cuentaContable', 'lineas.producto'])
            ->orderByDesc('id')
            ->get();

        $porVendedor = $ventas->groupBy(fn (VentaTienda $v): int => (int) ($v->user_id ?? 0));
        $porMetodo = $ventas->groupBy('metodo_pago');

        $totalesMetodo = [
            VentaTienda::METODO_EFECTIVO => round($porMetodo->get(VentaTienda::METODO_EFECTIVO, collect())->sum(fn (VentaTienda $v) => (float) $v->importe_total), 2),
            VentaTienda::METODO_TARJETA => round($porMetodo->get(VentaTienda::METODO_TARJETA, collect())->sum(fn (VentaTienda $v) => (float) $v->importe_total), 2),
            VentaTienda::METODO_BIZUM => round($porMetodo->get(VentaTienda::METODO_BIZUM, collect())->sum(fn (VentaTienda $v) => (float) $v->importe_total), 2),
        ];

        $userIds = $porVendedor->keys()->filter(fn ($k) => $k > 0)->values();
        $users = User::query()->whereIn('id', $userIds)->pluck('name', 'id');

        return view('tienda.ventas-dia.index', [
            'fecha' => $fecha->toDateString(),
            'ventas' => $ventas,
            'porVendedor' => $porVendedor,
            'porMetodo' => $porMetodo,
            'totalesMetodo' => $totalesMetodo,
            'users' => $users,
            'totalDia' => round($ventas->sum(fn (VentaTienda $v) => (float) $v->importe_total), 2),
        ]);
    }
}
