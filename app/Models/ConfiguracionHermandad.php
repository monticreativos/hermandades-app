<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConfiguracionHermandad extends Model
{
    protected $table = 'configuracion_hermandad';

    protected $fillable = [
        'nombre_hermandad',
        'nombre_corto',
        'cif',
        'direccion',
        'localidad',
        'cp',
        'provincia',
        'telefono',
        'email_contacto',
        'iban_cuotas',
        'bic_swift',
        'escudo_path',
        'censo_antiguedad_anos',
        'importe_cuota_anual_defecto',
        'firma_secretario_path',
        'firma_mayordomo_path',
        'sello_hermandad_path',
    ];

    protected $casts = [
        'censo_antiguedad_anos' => 'integer',
        'importe_cuota_anual_defecto' => 'decimal:2',
    ];
}
