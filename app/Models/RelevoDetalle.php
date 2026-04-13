<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RelevoDetalle extends Model
{
    protected $table = 'relevo_detalles';

    protected $fillable = [
        'relevo_id',
        'punto',
        'hora_desde',
        'hora_hasta',
        'turno',
        'hermano_id',
        'notas',
    ];

    public function relevo(): BelongsTo
    {
        return $this->belongsTo(RelevoCuadrilla::class, 'relevo_id');
    }

    public function hermano(): BelongsTo
    {
        return $this->belongsTo(Hermano::class);
    }
}
