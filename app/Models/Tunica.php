<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Tunica extends Model
{
    public const ESTADO_DISPONIBLE = 'Disponible';

    public const ESTADO_PRESTADA = 'Prestada';

    public const ESTADO_REPARACION = 'En reparación';

    public const ESTADO_BAJA = 'Baja';

    protected $fillable = [
        'codigo',
        'talla',
        'estado',
        'hermano_id',
        'fianza',
        'fecha_prestamo',
        'fecha_devolucion',
        'notas',
    ];

    protected $casts = [
        'fianza' => 'decimal:2',
        'fecha_prestamo' => 'date',
        'fecha_devolucion' => 'date',
    ];

    public function hermano(): BelongsTo
    {
        return $this->belongsTo(Hermano::class);
    }
}
