<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RelevoCuadrilla extends Model
{
    protected $table = 'relevos_cuadrilla';

    protected $fillable = [
        'cuadrilla_id',
        'titulo',
        'fecha_salida',
        'notas',
    ];

    protected function casts(): array
    {
        return [
            'fecha_salida' => 'date',
        ];
    }

    public function cuadrilla(): BelongsTo
    {
        return $this->belongsTo(Cuadrilla::class);
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(RelevoDetalle::class, 'relevo_id');
    }
}
