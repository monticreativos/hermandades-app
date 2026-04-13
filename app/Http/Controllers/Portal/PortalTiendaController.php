<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\ProductoTienda;
use App\Models\VentaTienda;
use App\Services\Tienda\PedidoTiendaPortalService;
use App\Services\Tienda\RegistrarVentaTiendaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PortalTiendaController extends Controller
{
    private const SESSION_CART = 'portal_tienda_carrito';

    public function index(): View
    {
        $productos = ProductoTienda::query()
            ->where('activo', true)
            ->where('stock_actual', '>', 0)
            ->orderBy('categoria')
            ->orderBy('nombre')
            ->get();

        $carrito = $this->obtenerCarrito();
        $lineasVista = $this->lineasCarritoVista($carrito);

        return view('portal.tienda.index', [
            'productos' => $productos,
            'carrito' => $lineasVista,
            'totalCarrito' => $lineasVista->sum('subtotal'),
            'categorias' => ProductoTienda::categorias(),
        ]);
    }

    public function agregar(Request $request, ProductoTienda $productoTienda): RedirectResponse
    {
        if (! $productoTienda->activo || $productoTienda->stock_actual < 1) {
            return back()->with('error', 'Producto no disponible.');
        }

        $qty = max(1, min((int) $request->input('cantidad', 1), $productoTienda->stock_actual));
        $cart = $this->obtenerCarrito();
        $key = (string) $productoTienda->id;
        $cart[$key] = ($cart[$key] ?? 0) + $qty;
        if ($cart[$key] > $productoTienda->stock_actual) {
            $cart[$key] = $productoTienda->stock_actual;
        }
        session([self::SESSION_CART => $cart]);

        return back()->with('status', 'Artículo añadido al carrito.');
    }

    public function quitar(ProductoTienda $productoTienda): RedirectResponse
    {
        $cart = $this->obtenerCarrito();
        unset($cart[(string) $productoTienda->id]);
        session([self::SESSION_CART => $cart]);

        return back()->with('status', 'Artículo quitado del carrito.');
    }

    public function vaciar(): RedirectResponse
    {
        session()->forget(self::SESSION_CART);

        return back()->with('status', 'Carrito vaciado.');
    }

    public function reservar(PedidoTiendaPortalService $service): RedirectResponse
    {
        $hermano = Auth::guard('portal')->user()->hermano;
        $items = $this->carritoComoItems();
        if ($items === []) {
            return back()->with('error', 'El carrito está vacío.');
        }

        try {
            $pedido = $service->crearReserva($hermano, $items);
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        session()->forget(self::SESSION_CART);

        return back()->with('status', 'Reserva confirmada. Código: '.$pedido->uuid.' — Pase por la Casa Hermandad para pagar y recoger.');
    }

    public function bizum(RegistrarVentaTiendaService $ventaService): RedirectResponse
    {
        $hermano = Auth::guard('portal')->user()->hermano;
        $items = $this->carritoComoItems();
        if ($items === []) {
            return back()->with('error', 'El carrito está vacío.');
        }

        try {
            $venta = $ventaService->registrar(
                null,
                $items,
                VentaTienda::METODO_BIZUM,
                $hermano,
                false,
                'tienda_portal_bizum'
            );
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        session()->forget(self::SESSION_CART);

        return back()->with('status', 'Pago por Bizum registrado. Recoja su pedido en la Casa Hermandad (ref. '.$venta->folio.').');
    }

    /**
     * @return array<string, int>
     */
    private function obtenerCarrito(): array
    {
        return session(self::SESSION_CART, []);
    }

    /**
     * @return list<array{producto_id: int, cantidad: int}>
     */
    private function carritoComoItems(): array
    {
        $cart = $this->obtenerCarrito();
        $items = [];
        foreach ($cart as $pid => $qty) {
            $items[] = ['producto_id' => (int) $pid, 'cantidad' => max(1, (int) $qty)];
        }

        return $items;
    }

    /**
     * @param  array<string, int>  $cart
     * @return Collection<int, object>
     */
    private function lineasCarritoVista(array $cart): Collection
    {
        if ($cart === []) {
            return collect();
        }
        $ids = array_map('intval', array_keys($cart));
        $productos = ProductoTienda::query()->whereIn('id', $ids)->get()->keyBy('id');

        return collect($cart)->map(function (int $qty, string $pid) use ($productos) {
            $p = $productos->get((int) $pid);
            if (! $p) {
                return null;
            }
            $qty = min($qty, $p->stock_actual);

            return (object) [
                'producto' => $p,
                'cantidad' => $qty,
                'subtotal' => round((float) $p->precio_venta * $qty, 2),
            ];
        })->filter()->values();
    }
}
