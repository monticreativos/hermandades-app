<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CuadrillaAviso extends Model
{
    protected $table = 'cuadrilla_avisos';

    protected $fillable = [
        'cuadrilla_id',
        'user_id',
        'titulo',
        'mensaje',
        'enviado_en',
    ];

    protected function casts(): array
    {
        return [
            'enviado_en' => 'datetime',
        ];
    }

    public function cuadrilla(): BelongsTo
    {
        return $this->belongsTo(Cuadrilla::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
