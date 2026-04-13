<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RemesaSepa extends Model
{
    protected $table = 'remesas_sepa';

    public const ESTADO_BORRADOR = 'borrador';

    public const ESTADO_ENVIADA = 'enviada';

    public const ESTADO_CONCILIACION_PARCIAL = 'conciliacion_parcial';

    public const ESTADO_CONCILIADA = 'conciliada';

    protected $fillable = [
        'ejercicio_id',
        'user_id',
        'fecha_emision',
        'año_referencia',
        'mes_referencia',
        'trimestre_referencia',
        'etiqueta_periodo',
        'numero_recibos',
        'importe_total',
        'archivo_xml_path',
        'msg_id',
        'pmt_inf_id',
        'estado',
        'asiento_conciliacion_id',
        'notas',
    ];

    protected function casts(): array
    {
        return [
            'fecha_emision' => 'date',
            'importe_total' => 'decimal:2',
        ];
    }

    public function ejercicio(): BelongsTo
    {
        return $this->belongsTo(Ejercicio::class);
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function recibos(): HasMany
    {
        return $this->hasMany(RemesaRecibo::class, 'remesa_id');
    }

    public function importacionesRespuesta(): HasMany
    {
        return $this->hasMany(ImportacionRespuestaBanco::class, 'remesa_id');
    }

    public function asientoConciliacion(): BelongsTo
    {
        return $this->belongsTo(Asiento::class, 'asiento_conciliacion_id');
    }
}
