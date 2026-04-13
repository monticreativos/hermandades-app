<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CategoriaPatrimonio extends Model
{
    protected $table = 'categorias_patrimonio';

    protected $fillable = [
        'nombre',
    ];

    public function enseres(): HasMany
    {
        return $this->hasMany(Enser::class, 'categoria_id');
    }
}
