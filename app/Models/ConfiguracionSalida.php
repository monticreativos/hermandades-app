<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;

class ConfiguracionSalida extends Model
{
    protected $table = 'configuracion_salidas';

    protected $fillable = [
        'año',
        'fecha_salida',
        'donativo_defecto',
        'fecha_inicio_reparto',
        'fecha_fin_reparto',
        'notas',
        'activa',
    ];

    protected $casts = [
        'año' => 'integer',
        'fecha_salida' => 'date',
        'donativo_defecto' => 'decimal:2',
        'fecha_inicio_reparto' => 'date',
        'fecha_fin_reparto' => 'date',
        'activa' => 'boolean',
    ];

    /**
     * Indica si hoy (o la fecha dada) cae dentro del periodo de reparto de papeletas.
     */
    public function repartoAbierto(?CarbonInterface $fecha = null): bool
    {
        if (! $this->activa || ! $this->fecha_inicio_reparto || ! $this->fecha_fin_reparto) {
            return false;
        }

        $f = ($fecha ?? now())->copy()->startOfDay();

        return $f->greaterThanOrEqualTo($this->fecha_inicio_reparto->copy()->startOfDay())
            && $f->lessThanOrEqualTo($this->fecha_fin_reparto->copy()->endOfDay());
    }
}
