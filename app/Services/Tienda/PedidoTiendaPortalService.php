<?php

namespace App\Services\Tienda;

use App\Models\Hermano;
use App\Models\PedidoTiendaPortal;
use App\Models\PedidoTiendaPortalLinea;
use App\Models\ProductoTienda;
use Illuminate\Support\Facades\DB;

class PedidoTiendaPortalService
{
    /**
     * @param  array<int, array{producto_id: int, cantidad: int}>  $items
     */
    public function crearReserva(Hermano $hermano, array $items): PedidoTiendaPortal
    {
        if ($items === []) {
            throw new \InvalidArgumentException('Seleccione al menos un artículo.');
        }

        return DB::transaction(function () use ($hermano, $items): PedidoTiendaPortal {
            $ids = array_column($items, 'producto_id');
            $productos = ProductoTienda::query()
                ->whereIn('id', $ids)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $totalTtc = 0.0;
            $lineas = [];

            foreach ($items as $row) {
                $pid = (int) $row['producto_id'];
                $qty = max(1, (int) $row['cantidad']);
                $p = $productos->get($pid);
                if (! $p || ! $p->activo) {
                    throw new \RuntimeException('Producto no disponible.');
                }
                if ($p->stock_actual < $qty) {
                    throw new \RuntimeException('Stock insuficiente para «'.$p->nombre.'».');
                }

                $unit = (float) $p->precio_venta;
                $sub = round($unit * $qty, 2);
                $totalTtc += $sub;
                $lineas[] = [
                    'producto' => $p,
                    'cantidad' => $qty,
                    'precio_unitario_ttc' => $unit,
                    'iva_porcentaje' => (float) $p->iva_porcentaje,
                    'subtotal_ttc' => $sub,
                ];
            }

            $pedido = PedidoTiendaPortal::query()->create([
                'hermano_id' => $hermano->id,
                'estado' => PedidoTiendaPortal::ESTADO_RESERVADO,
                'total_ttc' => round($totalTtc, 2),
            ]);

            foreach ($lineas as $l) {
                PedidoTiendaPortalLinea::query()->create([
                    'pedido_tienda_portal_id' => $pedido->id,
                    'producto_tienda_id' => $l['producto']->id,
                    'cantidad' => $l['cantidad'],
                    'precio_unitario_ttc' => $l['precio_unitario_ttc'],
                    'iva_porcentaje' => $l['iva_porcentaje'],
                    'subtotal_ttc' => $l['subtotal_ttc'],
                ]);
                $l['producto']->decrement('stock_actual', $l['cantidad']);
            }

            return $pedido->load('lineas.producto');
        });
    }

    public function cancelarReserva(PedidoTiendaPortal $pedido): void
    {
        if ($pedido->estado !== PedidoTiendaPortal::ESTADO_RESERVADO) {
            throw new \RuntimeException('Solo se pueden cancelar reservas activas.');
        }

        DB::transaction(function () use ($pedido): void {
            $pedido->load('lineas');
            foreach ($pedido->lineas as $linea) {
                ProductoTienda::query()
                    ->whereKey($linea->producto_tienda_id)
                    ->lockForUpdate()
                    ->first()
                    ?->increment('stock_actual', $linea->cantidad);
            }
            $pedido->forceFill(['estado' => PedidoTiendaPortal::ESTADO_CANCELADO])->save();
        });
    }
}
