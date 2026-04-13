<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ContactoExterno extends Model
{
    protected $table = 'contactos_externos';

    protected $fillable = [
        'nombre',
        'entidad_institucion',
        'cargo',
        'email',
        'telefono',
        'direccion',
        'categoria',
    ];

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(ContactoExternoTag::class, 'contacto_externo_tag', 'contacto_externo_id', 'tag_id')->withTimestamps();
    }

    public function registrosDocumentales(): HasMany
    {
        return $this->hasMany(SecretariaRegistroDocumental::class, 'contacto_externo_id');
    }
}
