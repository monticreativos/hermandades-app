<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Loteria extends Model
{
    protected $fillable = [
        'sorteo',
        'numero',
        'serie_fraccion',
        'total_participaciones',
        'precio_participacion',
        'donativo',
        'observaciones',
    ];

    protected $casts = [
        'total_participaciones' => 'integer',
        'precio_participacion' => 'decimal:2',
        'donativo' => 'decimal:2',
    ];

    public function asignaciones(): HasMany
    {
        return $this->hasMany(LoteriaAsignacion::class);
    }

    public function participacionesAsignadas(): int
    {
        return (int) $this->asignaciones()->sum('participaciones');
    }

    public function participacionesDisponibles(): int
    {
        return max(0, $this->total_participaciones - $this->participacionesAsignadas());
    }
}
