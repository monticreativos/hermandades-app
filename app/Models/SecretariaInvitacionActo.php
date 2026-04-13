<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SecretariaInvitacionActo extends Model
{
    protected $table = 'secretaria_invitaciones_acto';

    protected $fillable = [
        'acto_id',
        'entidad_externa_id',
        'hermano_id',
        'contacto_externo_id',
        'nombre_invitado',
        'categoria_fuente',
        'estado_confirmacion',
        'fila',
        'banco',
        'orden_protocolo',
        'notas',
    ];

    public function acto(): BelongsTo
    {
        return $this->belongsTo(SecretariaActoProtocolo::class, 'acto_id');
    }

    public function entidad(): BelongsTo
    {
        return $this->belongsTo(SecretariaEntidadExterna::class, 'entidad_externa_id');
    }

    public function hermano(): BelongsTo
    {
        return $this->belongsTo(Hermano::class, 'hermano_id');
    }

    public function contactoExterno(): BelongsTo
    {
        return $this->belongsTo(ContactoExterno::class, 'contacto_externo_id');
    }
}
