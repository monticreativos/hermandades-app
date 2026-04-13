<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VentaTienda extends Model
{
    protected $table = 'ventas_tienda';

    public const METODO_EFECTIVO = 'efectivo';

    public const METODO_TARJETA = 'tarjeta';

    public const METODO_BIZUM = 'bizum';

    protected $fillable = [
        'folio',
        'user_id',
        'hermano_id',
        'venta_anonima',
        'metodo_pago',
        'importe_total',
        'total_base',
        'total_iva',
        'asiento_id',
        'pedido_portal_uuid',
        'notas',
    ];

    protected function casts(): array
    {
        return [
            'venta_anonima' => 'boolean',
            'importe_total' => 'decimal:2',
            'total_base' => 'decimal:2',
            'total_iva' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function hermano(): BelongsTo
    {
        return $this->belongsTo(Hermano::class);
    }

    public function asiento(): BelongsTo
    {
        return $this->belongsTo(Asiento::class);
    }

    public function lineas(): HasMany
    {
        return $this->hasMany(VentaTiendaLinea::class, 'venta_tienda_id');
    }
}
