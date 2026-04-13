<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PedidoTiendaPortalLinea extends Model
{
    protected $table = 'pedido_tienda_portal_lineas';

    protected $fillable = [
        'pedido_tienda_portal_id',
        'producto_tienda_id',
        'cantidad',
        'precio_unitario_ttc',
        'iva_porcentaje',
        'subtotal_ttc',
    ];

    protected function casts(): array
    {
        return [
            'precio_unitario_ttc' => 'decimal:2',
            'iva_porcentaje' => 'decimal:2',
            'subtotal_ttc' => 'decimal:2',
        ];
    }

    public function pedido(): BelongsTo
    {
        return $this->belongsTo(PedidoTiendaPortal::class, 'pedido_tienda_portal_id');
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(ProductoTienda::class, 'producto_tienda_id');
    }
}
