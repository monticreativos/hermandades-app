<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Asiento extends Model
{
    protected $fillable = [
        'ejercicio_id',
        'numero_asiento',
        'fecha',
        'glosa',
        'movimiento_rapido',
        'canal_origen',
        'categoria_economia',
        'hermano_id',
        'apt_modelo_182',
        'operacion_exenta_iva',
        'renta_is_exenta',
        'base_imponible',
        'cuota_iva',
    ];

    protected $casts = [
        'fecha' => 'date',
        'movimiento_rapido' => 'boolean',
        'apt_modelo_182' => 'boolean',
        'operacion_exenta_iva' => 'boolean',
        'renta_is_exenta' => 'boolean',
        'base_imponible' => 'decimal:2',
        'cuota_iva' => 'decimal:2',
    ];

    public function ejercicio(): BelongsTo
    {
        return $this->belongsTo(Ejercicio::class);
    }

    public function hermano(): BelongsTo
    {
        return $this->belongsTo(Hermano::class);
    }

    public function apuntes(): HasMany
    {
        return $this->hasMany(Apunte::class)->orderBy('id');
    }

    public function documentosGasto(): HasMany
    {
        return $this->hasMany(DocumentoGasto::class)->orderBy('orden_linea');
    }

    /**
     * Importe en cuenta de tesorería (570/572): debe si entra caja/banco, haber si sale.
     */
    public function importeTesoreriaPrincipal(): float
    {
        $this->loadMissing('apuntes.cuentaContable');
        foreach ($this->apuntes as $apunte) {
            $codigo = $apunte->cuentaContable?->codigo ?? '';
            if (! str_starts_with($codigo, '570') && ! str_starts_with($codigo, '572')) {
                continue;
            }
            $debe = (float) $apunte->debe;
            $haber = (float) $apunte->haber;
            if ($debe > 0.001) {
                return round($debe, 2);
            }
            if ($haber > 0.001) {
                return round($haber, 2);
            }
        }

        return 0.0;
    }

    /** Suma de importes al haber en cuentas de ingreso del asiento. */
    public function totalIngresosHaber(): float
    {
        $this->loadMissing('apuntes.cuentaContable');

        return round((float) $this->apuntes
            ->filter(fn (Apunte $a) => ($a->cuentaContable?->tipo ?? '') === 'Ingreso' && (float) $a->haber > 0)
            ->sum('haber'), 2);
    }
}
