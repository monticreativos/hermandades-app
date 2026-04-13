<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CategoriaTienda extends Model
{
    protected $table = 'categorias_tienda';

    protected $fillable = [
        'nombre',
        'orden',
        'activa',
    ];

    protected function casts(): array
    {
        return [
            'orden' => 'integer',
            'activa' => 'boolean',
        ];
    }
}
