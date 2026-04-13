<?php

namespace App\Services\Tienda;

use App\Models\Actividad;
use App\Models\Hermano;
use App\Models\PedidoTiendaPortal;
use App\Models\ProductoTienda;
use App\Models\User;
use App\Models\VentaTienda;
use App\Models\VentaTiendaLinea;
use App\Support\RegistroActividad;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RegistrarVentaTiendaService
{
    public function __construct(
        private readonly VentaTiendaContabilidadService $ventaTiendaContabilidadService
    ) {}

    /**
     * @param  array<int, array{producto_id: int, cantidad: int}>  $items
     */
    public function registrar(
        ?User $cajero,
        array $items,
        string $metodoPago,
        ?Hermano $hermano,
        bool $ventaAnonima,
        string $canalOrigen = 'tienda_tpv',
        ?PedidoTiendaPortal $pedidoReserva = null
    ): VentaTienda {
        if (! in_array($metodoPago, [VentaTienda::METODO_EFECTIVO, VentaTienda::METODO_TARJETA, VentaTienda::METODO_BIZUM], true)) {
            throw new \InvalidArgumentException('Método de pago no válido.');
        }

        if ($items === []) {
            throw new \InvalidArgumentException('El carrito está vacío.');
        }

        if ($ventaAnonima) {
            $hermano = null;
        } elseif (! $hermano) {
            throw new \InvalidArgumentException('Indique un hermano o marque venta anónima.');
        }

        $folio = 'TPV-'.now()->format('YmdHis').'-'.strtoupper(Str::random(4));

        return DB::transaction(function () use ($cajero, $items, $metodoPago, $hermano, $ventaAnonima, $canalOrigen, $pedidoReserva, $folio): VentaTienda {
            $ids = array_column($items, 'producto_id');
            $productos = ProductoTienda::query()
                ->whereIn('id', $ids)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $lineasData = [];
            $totalTtc = 0.0;
            $totalBase = 0.0;
            $totalIva = 0.0;

            foreach ($items as $row) {
                $pid = (int) $row['producto_id'];
                $qty = max(1, (int) $row['cantidad']);
                $p = $productos->get($pid);
                if (! $p || ! $p->activo) {
                    throw new \RuntimeException('Producto no disponible: ID '.$pid);
                }

                if (! $pedidoReserva && $p->stock_actual < $qty) {
                    throw new \RuntimeException('Stock insuficiente para «'.$p->nombre.'» (disponible: '.$p->stock_actual.').');
                }

                $unitTtc = (float) $p->precio_venta;
                $ivaPct = (float) $p->iva_porcentaje;
                $lineTtc = round($unitTtc * $qty, 2);
                $desg = ProductoTienda::desglosarLineaTtc($lineTtc, $ivaPct);

                $lineasData[] = [
                    'producto' => $p,
                    'cantidad' => $qty,
                    'precio_unitario_ttc' => $unitTtc,
                    'iva_porcentaje' => $ivaPct,
                    'base_imponible_linea' => $desg['base'],
                    'cuota_iva_linea' => $desg['iva'],
                    'total_linea' => $lineTtc,
                ];

                $totalTtc += $lineTtc;
                $totalBase += $desg['base'];
                $totalIva += $desg['iva'];
            }

            $totalTtc = round($totalTtc, 2);
            $totalBase = round($totalBase, 2);
            $totalIva = round($totalIva, 2);

            $venta = VentaTienda::query()->create([
                'folio' => $folio,
                'user_id' => $cajero?->id,
                'hermano_id' => $hermano?->id,
                'venta_anonima' => $ventaAnonima,
                'metodo_pago' => $metodoPago,
                'importe_total' => $totalTtc,
                'total_base' => $totalBase,
                'total_iva' => $totalIva,
                'pedido_portal_uuid' => $pedidoReserva?->uuid,
                'notas' => null,
            ]);

            foreach ($lineasData as $ld) {
                /** @var ProductoTienda $p */
                $p = $ld['producto'];
                VentaTiendaLinea::query()->create([
                    'venta_tienda_id' => $venta->id,
                    'producto_tienda_id' => $p->id,
                    'cantidad' => $ld['cantidad'],
                    'precio_unitario_ttc' => $ld['precio_unitario_ttc'],
                    'iva_porcentaje' => $ld['iva_porcentaje'],
                    'base_imponible_linea' => $ld['base_imponible_linea'],
                    'cuota_iva_linea' => $ld['cuota_iva_linea'],
                    'total_linea' => $ld['total_linea'],
                    'precio_coste_unitario_snapshot' => $p->precio_coste,
                ]);

                if (! $pedidoReserva) {
                    $p->decrement('stock_actual', $ld['cantidad']);
                }
            }

            $fecha = Carbon::now();
            $glosa = 'Venta tienda '.$folio.($hermano ? ' — n.º '.$hermano->numero_hermano : ' — público');

            $asiento = $this->ventaTiendaContabilidadService->crearAsientoVenta(
                $fecha,
                $totalTtc,
                $totalBase,
                $totalIva,
                $metodoPago,
                $hermano?->id,
                $glosa,
                $canalOrigen
            );

            $venta->forceFill(['asiento_id' => $asiento->id])->save();

            if ($pedidoReserva) {
                $pedidoReserva->forceFill([
                    'estado' => PedidoTiendaPortal::ESTADO_ENTREGADO,
                    'venta_tienda_id' => $venta->id,
                ])->save();
            }

            if ($hermano) {
                RegistroActividad::registrar(
                    'venta_tienda_hermano',
                    'Venta en tienda '.$folio.' por '.number_format($totalTtc, 2, ',', '.').' € ('.$metodoPago.') — hermano n.º '.$hermano->numero_hermano.'.'
                );
                Actividad::query()->create([
                    'user_id' => $cajero?->id,
                    'accion' => 'venta_tienda',
                    'descripcion' => 'Tienda: '.$folio.' — '.number_format($totalTtc, 2, ',', '.').' € — n.º '.$hermano->numero_hermano,
                ]);
            }

            return $venta->load('lineas');
        });
    }

    /**
     * Cobro en TPV de un pedido reservado desde el portal (el stock ya se descontó al reservar).
     */
    public function registrarDesdePedidoReserva(User $cajero, PedidoTiendaPortal $pedido, string $metodoPago): VentaTienda
    {
        if ($pedido->estado !== PedidoTiendaPortal::ESTADO_RESERVADO) {
            throw new \RuntimeException('El pedido no está en estado reservado.');
        }

        $pedido->load('lineas');
        $items = $pedido->lineas->map(fn ($l) => [
            'producto_id' => $l->producto_tienda_id,
            'cantidad' => $l->cantidad,
        ])->all();

        $hermano = $pedido->hermano;
        if (! $hermano) {
            throw new \RuntimeException('Pedido sin hermano asociado.');
        }

        return $this->registrar(
            $cajero,
            $items,
            $metodoPago,
            $hermano,
            false,
            'tienda_tpv_pedido_reserva',
            $pedido
        );
    }
}
