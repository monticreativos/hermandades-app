<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SecretariaPlantillaDocumental extends Model
{
    protected $table = 'secretaria_plantillas_documentales';

    protected $fillable = [
        'nombre',
        'tipo',
        'cuerpo_plantilla',
        'marca_agua',
        'marca_agua_path',
        'activa',
        'creado_por_user_id',
    ];

    protected function casts(): array
    {
        return [
            'activa' => 'boolean',
        ];
    }

    public function creadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creado_por_user_id');
    }
}
