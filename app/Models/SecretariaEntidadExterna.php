<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SecretariaEntidadExterna extends Model
{
    protected $table = 'secretaria_entidades_externas';

    protected $fillable = [
        'nombre',
        'tipo',
        'contacto',
        'email',
        'telefono',
        'notas',
    ];

    public function invitaciones(): HasMany
    {
        return $this->hasMany(SecretariaInvitacionActo::class, 'entidad_externa_id');
    }
}
