<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VentaTiendaLinea extends Model
{
    protected $table = 'venta_tienda_lineas';

    protected $fillable = [
        'venta_tienda_id',
        'producto_tienda_id',
        'cantidad',
        'precio_unitario_ttc',
        'iva_porcentaje',
        'base_imponible_linea',
        'cuota_iva_linea',
        'total_linea',
        'precio_coste_unitario_snapshot',
    ];

    protected function casts(): array
    {
        return [
            'precio_unitario_ttc' => 'decimal:2',
            'iva_porcentaje' => 'decimal:2',
            'base_imponible_linea' => 'decimal:2',
            'cuota_iva_linea' => 'decimal:2',
            'total_linea' => 'decimal:2',
            'precio_coste_unitario_snapshot' => 'decimal:2',
        ];
    }

    public function venta(): BelongsTo
    {
        return $this->belongsTo(VentaTienda::class, 'venta_tienda_id');
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(ProductoTienda::class, 'producto_tienda_id');
    }
}
