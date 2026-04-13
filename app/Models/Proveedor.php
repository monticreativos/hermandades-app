<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Proveedor extends Model
{
    public const TIPO_JURIDICA = 'juridica';

    public const TIPO_FISICA = 'fisica';

    public const TIPO_AUTONOMO = 'autonomo';

    public const REGIMEN_GENERAL = 'general';

    public const REGIMEN_RECARGO = 'recargo_equivalencia';

    public const REGIMEN_EXENTO = 'exento';

    public const REGIMEN_NO_SUJETO = 'no_sujeto';

    public const REGIMEN_INTRACOMUNITARIO = 'intracomunitario';

    public const REGIMEN_OTROS = 'otros';

    protected $table = 'proveedores';

    protected $fillable = [
        'razon_social',
        'nombre_comercial',
        'tipo_persona',
        'nif_cif',
        'direccion',
        'codigo_postal',
        'municipio',
        'provincia',
        'pais',
        'telefono',
        'email',
        'regimen_iva',
        'iban',
        'notas',
        'cuenta_contable_id',
    ];

    public function documentosGasto(): HasMany
    {
        return $this->hasMany(DocumentoGasto::class, 'proveedor_id');
    }

    public function cuentaContable(): BelongsTo
    {
        return $this->belongsTo(CuentaContable::class, 'cuenta_contable_id');
    }

    public function etiquetaListado(): string
    {
        $nif = $this->nif_cif ? strtoupper($this->nif_cif).' — ' : '';

        return $nif.$this->razon_social;
    }
}
