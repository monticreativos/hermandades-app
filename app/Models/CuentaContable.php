<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class CuentaContable extends Model
{
    protected $table = 'cuentas_contables';

    protected $fillable = [
        'codigo',
        'nombre',
        'tipo',
        'hermano_id',
        'proveedor_id',
    ];

    public function apuntes(): HasMany
    {
        return $this->hasMany(Apunte::class);
    }

    public function hermano(): BelongsTo
    {
        return $this->belongsTo(Hermano::class);
    }

    public function proveedor(): BelongsTo
    {
        return $this->belongsTo(Proveedor::class);
    }

    public function hermanoVinculado(): HasOne
    {
        return $this->hasOne(Hermano::class, 'cuenta_contable_id');
    }

    public function proveedorVinculado(): HasOne
    {
        return $this->hasOne(Proveedor::class, 'cuenta_contable_id');
    }
}
