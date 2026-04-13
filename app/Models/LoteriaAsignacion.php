<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoteriaAsignacion extends Model
{
    protected $table = 'loteria_asignaciones';

    protected $fillable = [
        'loteria_id',
        'hermano_id',
        'participaciones',
        'referencia_taco',
        'importe_a_cobrar',
        'cobrado',
        'fecha_cobro',
        'notas',
    ];

    protected $casts = [
        'participaciones' => 'integer',
        'importe_a_cobrar' => 'decimal:2',
        'cobrado' => 'boolean',
        'fecha_cobro' => 'date',
    ];

    public function loteria(): BelongsTo
    {
        return $this->belongsTo(Loteria::class);
    }

    public function hermano(): BelongsTo
    {
        return $this->belongsTo(Hermano::class);
    }
}
