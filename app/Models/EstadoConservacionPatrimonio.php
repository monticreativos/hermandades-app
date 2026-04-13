<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EstadoConservacionPatrimonio extends Model
{
    protected $table = 'estados_conservacion_patrimonio';

    protected $fillable = [
        'nombre',
    ];

    public function enseres(): HasMany
    {
        return $this->hasMany(Enser::class, 'estado_conservacion_id');
    }
}
