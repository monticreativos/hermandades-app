<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Familia extends Model
{
    protected $table = 'familias';

    protected $fillable = [
        'nombre',
        'pago_unificado',
        'pagador_hermano_id',
    ];

    protected function casts(): array
    {
        return [
            'pago_unificado' => 'boolean',
        ];
    }

    public function miembros(): BelongsToMany
    {
        return $this->belongsToMany(Hermano::class, 'familia_hermano')
            ->withPivot(['parentesco'])
            ->withTimestamps();
    }

    public function pagador(): BelongsTo
    {
        return $this->belongsTo(Hermano::class, 'pagador_hermano_id');
    }
}
