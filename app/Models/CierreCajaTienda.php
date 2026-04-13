<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CierreCajaTienda extends Model
{
    protected $table = 'cierres_caja_tienda';

    protected $fillable = [
        'fecha',
        'user_id',
        'teorico_efectivo',
        'teorico_tarjeta',
        'teorico_bizum',
        'saldo_inicial_efectivo',
        'efectivo_esperado_cierre',
        'conteo_efectivo_fisico',
        'diferencia_efectivo',
        'notas',
    ];

    protected function casts(): array
    {
        return [
            'fecha' => 'date',
            'teorico_efectivo' => 'decimal:2',
            'teorico_tarjeta' => 'decimal:2',
            'teorico_bizum' => 'decimal:2',
            'saldo_inicial_efectivo' => 'decimal:2',
            'efectivo_esperado_cierre' => 'decimal:2',
            'conteo_efectivo_fisico' => 'decimal:2',
            'diferencia_efectivo' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
