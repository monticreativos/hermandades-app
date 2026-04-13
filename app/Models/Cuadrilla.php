<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cuadrilla extends Model
{
    protected $table = 'cuadrillas';

    public const PASO_CRISTO = 'cristo';

    public const PASO_VIRGEN = 'virgen';

    protected $fillable = [
        'año',
        'nombre',
        'paso',
        'capataz_user_id',
        'numero_trabajaderas',
        'puestos_por_trabajadera',
        'notas',
        'activa',
    ];

    protected function casts(): array
    {
        return [
            'año' => 'integer',
            'numero_trabajaderas' => 'integer',
            'puestos_por_trabajadera' => 'integer',
            'activa' => 'boolean',
        ];
    }

    public function capataz(): BelongsTo
    {
        return $this->belongsTo(User::class, 'capataz_user_id');
    }

    public function costaleros(): HasMany
    {
        return $this->hasMany(CostaleroPerfil::class);
    }

    public function ensayos(): HasMany
    {
        return $this->hasMany(EnsayoCuadrilla::class);
    }

    public function relevos(): HasMany
    {
        return $this->hasMany(RelevoCuadrilla::class);
    }

    public function avisos(): HasMany
    {
        return $this->hasMany(CuadrillaAviso::class);
    }
}
