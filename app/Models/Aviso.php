<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Aviso extends Model
{
    public const ALCANCE_MASIVO = 'Masivo';

    public const ALCANCE_INDIVIDUAL = 'Individual';

    public const ALCANCE_SELECTIVO = 'Selectivo';

    protected $fillable = [
        'titulo',
        'cuerpo',
        'alcance',
        'solo_alta',
        'solo_portal',
        'urgente',
        'visible_tablon',
        'hermano_id',
        'creado_por_user_id',
        'enviado_en',
    ];

    protected function casts(): array
    {
        return [
            'solo_alta' => 'boolean',
            'solo_portal' => 'boolean',
            'urgente' => 'boolean',
            'visible_tablon' => 'boolean',
            'enviado_en' => 'datetime',
        ];
    }

    public function hermanoIndividual(): BelongsTo
    {
        return $this->belongsTo(Hermano::class, 'hermano_id');
    }

    public function creadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creado_por_user_id');
    }

    public function destinatarios(): HasMany
    {
        return $this->hasMany(AvisoHermano::class);
    }

    public function estaEnviado(): bool
    {
        return $this->enviado_en !== null;
    }
}
