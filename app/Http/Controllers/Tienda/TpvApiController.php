<?php

namespace App\Http\Controllers\Tienda;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tienda\CheckoutPedidoTpvRequest;
use App\Http\Requests\Tienda\StoreVentaTpvRequest;
use App\Models\Hermano;
use App\Models\PedidoTiendaPortal;
use App\Models\ProductoTienda;
use App\Services\Tienda\RegistrarVentaTiendaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TpvApiController extends Controller
{
    public function productos(Request $request): JsonResponse
    {
        $q = ProductoTienda::query()
            ->where('activo', true)
            ->orderBy('nombre');

        if ($request->filled('q')) {
            $s = '%'.$request->string('q').'%';
            $q->where(function ($w) use ($s): void {
                $w->where('nombre', 'like', $s)
                    ->orWhere('sku', 'like', $s);
            });
        }
        if ($request->filled('sku')) {
            $sku = trim($request->string('sku'));
            $q->where('sku', $sku);
        }
        if ($request->filled('categoria')) {
            $q->where('categoria', $request->string('categoria'));
        }

        $productos = $q->limit(120)->get()->map(fn (ProductoTienda $p): array => [
            'id' => $p->id,
            'nombre' => $p->nombre,
            'categoria' => $p->categoria,
            'precio_venta' => (float) $p->precio_venta,
            'iva_porcentaje' => (float) $p->iva_porcentaje,
            'stock_actual' => $p->stock_actual,
            'sku' => $p->sku,
            'imagen_url' => $p->urlImagen(),
        ]);

        return response()->json(['productos' => $productos]);
    }

    public function hermanos(Request $request): JsonResponse
    {
        $s = trim((string) $request->string('q'));
        if (mb_strlen($s) < 2) {
            return response()->json(['hermanos' => []]);
        }

        $like = '%'.$s.'%';
        $hermanos = Hermano::query()
            ->where('estado', 'Alta')
            ->where(function ($w) use ($like): void {
                $w->where('nombre', 'like', $like)
                    ->orWhere('apellidos', 'like', $like)
                    ->orWhere('dni', 'like', $like)
                    ->orWhere('numero_hermano', 'like', $like);
            })
            ->orderBy('numero_hermano')
            ->limit(20)
            ->get()
            ->map(fn (Hermano $h): array => [
                'id' => $h->id,
                'label' => 'N.º '.$h->numero_hermano.' — '.$h->apellidos.', '.$h->nombre,
                'numero_hermano' => $h->numero_hermano,
            ]);

        return response()->json(['hermanos' => $hermanos]);
    }

    public function pedido(string $uuid): JsonResponse
    {
        $pedido = PedidoTiendaPortal::query()
            ->where('uuid', $uuid)
            ->with(['lineas.producto', 'hermano'])
            ->first();

        if (! $pedido) {
            return response()->json(['error' => 'Pedido no encontrado'], 404);
        }

        return response()->json([
            'pedido' => [
                'uuid' => $pedido->uuid,
                'estado' => $pedido->estado,
                'total_ttc' => (float) $pedido->total_ttc,
                'hermano' => $pedido->hermano ? [
                    'id' => $pedido->hermano->id,
                    'numero_hermano' => $pedido->hermano->numero_hermano,
                    'nombre' => $pedido->hermano->nombreCompleto(),
                ] : null,
                'lineas' => $pedido->lineas->map(fn ($l) => [
                    'producto_id' => $l->producto_tienda_id,
                    'nombre' => $l->producto?->nombre,
                    'cantidad' => $l->cantidad,
                    'precio_unitario_ttc' => (float) $l->precio_unitario_ttc,
                    'subtotal_ttc' => (float) $l->subtotal_ttc,
                ]),
            ],
        ]);
    }

    public function checkout(StoreVentaTpvRequest $request, RegistrarVentaTiendaService $service): JsonResponse
    {
        $user = Auth::user();
        $items = $request->validated('items');
        $metodo = $request->validated('metodo_pago');
        $anonimo = $request->validated('venta_anonima');
        $hermano = null;
        if (! $anonimo && $request->filled('hermano_id')) {
            $hermano = Hermano::query()->find($request->integer('hermano_id'));
        }

        try {
            $venta = $service->registrar(
                $user,
                $items,
                $metodo,
                $hermano,
                $anonimo,
                'tienda_tpv'
            );
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }

        return response()->json([
            'ok' => true,
            'folio' => $venta->folio,
            'importe_total' => (float) $venta->importe_total,
            'asiento_id' => $venta->asiento_id,
            'ticket_url' => route('tienda.ventas.ticket', $venta),
        ]);
    }

    public function checkoutPedido(CheckoutPedidoTpvRequest $request, RegistrarVentaTiendaService $service): JsonResponse
    {
        $pedido = PedidoTiendaPortal::query()
            ->where('uuid', $request->validated('pedido_uuid'))
            ->firstOrFail();

        try {
            $venta = $service->registrarDesdePedidoReserva(
                Auth::user(),
                $pedido,
                $request->validated('metodo_pago')
            );
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }

        return response()->json([
            'ok' => true,
            'folio' => $venta->folio,
            'importe_total' => (float) $venta->importe_total,
            'ticket_url' => route('tienda.ventas.ticket', $venta),
        ]);
    }
}
