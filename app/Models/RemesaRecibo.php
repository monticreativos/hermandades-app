<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RemesaRecibo extends Model
{
    protected $table = 'remesa_recibos';

    public const ESTADO_PENDIENTE_BANCO = 'pendiente_banco';

    public const ESTADO_COBRADO = 'cobrado';

    public const ESTADO_DEVUELTO = 'devuelto';

    protected $fillable = [
        'remesa_id',
        'hermano_id',
        'end_to_end_id',
        'periodo_clave',
        'importe',
        'estado',
        'fecha_estado',
        'motivo_devolucion',
        'codigo_devolucion',
        'asiento_cobro_id',
        'comision_banco',
        'asiento_comision_id',
    ];

    protected function casts(): array
    {
        return [
            'importe' => 'decimal:2',
            'fecha_estado' => 'datetime',
            'comision_banco' => 'decimal:2',
        ];
    }

    public function remesa(): BelongsTo
    {
        return $this->belongsTo(RemesaSepa::class, 'remesa_id');
    }

    public function hermano(): BelongsTo
    {
        return $this->belongsTo(Hermano::class);
    }

    public function asientoCobro(): BelongsTo
    {
        return $this->belongsTo(Asiento::class, 'asiento_cobro_id');
    }
}
