<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactoExternoTag extends Model
{
    protected $table = 'contacto_externo_tags';

    protected $fillable = ['nombre'];
}
