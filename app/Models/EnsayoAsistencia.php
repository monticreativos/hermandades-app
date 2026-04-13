<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EnsayoAsistencia extends Model
{
    protected $table = 'ensayo_asistencias';

    protected $fillable = [
        'ensayo_id',
        'hermano_id',
        'asistio',
        'observacion',
    ];

    protected function casts(): array
    {
        return [
            'asistio' => 'boolean',
        ];
    }

    public function ensayo(): BelongsTo
    {
        return $this->belongsTo(EnsayoCuadrilla::class, 'ensayo_id');
    }

    public function hermano(): BelongsTo
    {
        return $this->belongsTo(Hermano::class);
    }
}
