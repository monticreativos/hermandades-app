<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class PedidoTiendaPortal extends Model
{
    protected $table = 'pedidos_tienda_portal';

    public const ESTADO_BORRADOR = 'borrador';

    public const ESTADO_RESERVADO = 'reservado';

    public const ESTADO_PAGADO = 'pagado';

    public const ESTADO_ENTREGADO = 'entregado';

    public const ESTADO_CANCELADO = 'cancelado';

    protected $fillable = [
        'uuid',
        'hermano_id',
        'estado',
        'total_ttc',
        'asiento_id',
        'venta_tienda_id',
    ];

    protected function casts(): array
    {
        return [
            'total_ttc' => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (PedidoTiendaPortal $p): void {
            if (empty($p->uuid)) {
                $p->uuid = (string) Str::uuid();
            }
        });
    }

    public function hermano(): BelongsTo
    {
        return $this->belongsTo(Hermano::class);
    }

    public function asiento(): BelongsTo
    {
        return $this->belongsTo(Asiento::class);
    }

    public function ventaTienda(): BelongsTo
    {
        return $this->belongsTo(VentaTienda::class);
    }

    public function lineas(): HasMany
    {
        return $this->hasMany(PedidoTiendaPortalLinea::class, 'pedido_tienda_portal_id');
    }
}
