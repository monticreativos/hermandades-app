<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EnsayoCuadrilla extends Model
{
    protected $table = 'ensayos_cuadrilla';

    protected $fillable = [
        'cuadrilla_id',
        'fecha',
        'hora_inicio',
        'lugar',
        'notas',
    ];

    protected function casts(): array
    {
        return [
            'fecha' => 'date',
        ];
    }

    public function cuadrilla(): BelongsTo
    {
        return $this->belongsTo(Cuadrilla::class);
    }

    public function asistencias(): HasMany
    {
        return $this->hasMany(EnsayoAsistencia::class, 'ensayo_id');
    }
}
