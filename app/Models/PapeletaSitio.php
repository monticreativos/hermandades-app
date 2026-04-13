<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PapeletaSitio extends Model
{
    protected $table = 'papeletas_sitio';

    public const ESTADO_SOLICITADA = 'Solicitada';

    public const ESTADO_EMITIDA = 'Emitida';

    public const ESTADO_ANULADA = 'Anulada';

    public const PUESTOS = [
        'Nazareno',
        'Costalero',
        'Monaguillo',
        'Acólito',
        'Capataz',
        'Músico',
        'Vara',
        'Insignia',
        'Acompañante de insignia',
        'Cirial',
        'Pertiguero',
        'Otro',
    ];

    protected $fillable = [
        'hermano_id',
        'ejercicio_id',
        'puesto',
        'insignia_id',
        'tramo',
        'donativo_pagado',
        'estado',
        'asistencia',
        'notas',
    ];

    protected $casts = [
        'donativo_pagado' => 'decimal:2',
        'asistencia' => 'boolean',
    ];

    public function hermano(): BelongsTo
    {
        return $this->belongsTo(Hermano::class);
    }

    public function ejercicio(): BelongsTo
    {
        return $this->belongsTo(Ejercicio::class);
    }

    public function insignia(): BelongsTo
    {
        return $this->belongsTo(Insignia::class);
    }
}
