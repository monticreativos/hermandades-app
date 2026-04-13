<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ejercicio extends Model
{
    public const ESTADO_ABIERTO = 'Abierto';

    public const ESTADO_CERRADO = 'Cerrado';

    protected $fillable = [
        'año',
        'estado',
    ];

    public function asientos(): HasMany
    {
        return $this->hasMany(Asiento::class);
    }

    public function estaAbierto(): bool
    {
        return $this->estado === self::ESTADO_ABIERTO;
    }
}
