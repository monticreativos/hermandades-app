<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SecretariaActoProtocolo extends Model
{
    protected $table = 'secretaria_actos_protocolo';

    protected $fillable = [
        'titulo',
        'fecha',
        'lugar',
        'descripcion',
    ];

    protected function casts(): array
    {
        return [
            'fecha' => 'date',
        ];
    }

    public function invitaciones(): HasMany
    {
        return $this->hasMany(SecretariaInvitacionActo::class, 'acto_id');
    }
}
