<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AperturaCajaTienda extends Model
{
    protected $table = 'aperturas_caja_tienda';

    protected $fillable = [
        'fecha',
        'user_id',
        'saldo_inicial_efectivo',
        'notas',
    ];

    protected function casts(): array
    {
        return [
            'fecha' => 'date',
            'saldo_inicial_efectivo' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
