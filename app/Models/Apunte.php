<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Apunte extends Model
{
    protected $fillable = [
        'asiento_id',
        'cuenta_contable_id',
        'debe',
        'haber',
        'concepto_detalle',
    ];

    protected $casts = [
        'debe' => 'decimal:2',
        'haber' => 'decimal:2',
    ];

    public function asiento(): BelongsTo
    {
        return $this->belongsTo(Asiento::class);
    }

    public function cuentaContable(): BelongsTo
    {
        return $this->belongsTo(CuentaContable::class);
    }

    /**
     * Movimientos de una subcuenta ordenados cronológicamente con saldo acumulado (ventana SQL).
     *
     * @return Collection<int, static>
     */
    public static function extractoSubcuentaConSaldo(int $cuentaContableId): Collection
    {
        return static::query()
            ->select('apuntes.*')
            ->join('asientos', 'asientos.id', '=', 'apuntes.asiento_id')
            ->where('apuntes.cuenta_contable_id', $cuentaContableId)
            ->orderBy('asientos.fecha')
            ->orderBy('asientos.numero_asiento')
            ->orderBy('asientos.ejercicio_id')
            ->orderBy('apuntes.id')
            ->selectRaw('SUM(apuntes.debe - apuntes.haber) OVER (
                ORDER BY asientos.fecha, asientos.numero_asiento, asientos.ejercicio_id, apuntes.id
            ) as saldo_acumulado')
            ->with(['asiento.ejercicio'])
            ->get();
    }
}
