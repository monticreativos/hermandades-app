<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SolicitudCambioDatos extends Model
{
    public const ESTADO_PENDIENTE = 'Pendiente';

    public const ESTADO_APROBADA = 'Aprobada';

    public const ESTADO_RECHAZADA = 'Rechazada';

    protected $table = 'solicitudes_cambio_datos';

    protected $fillable = [
        'hermano_id',
        'hermano_portal_cuenta_id',
        'ip_solicitud',
        'user_agent',
        'datos_solicitados',
        'estado',
        'motivo_rechazo',
        'procesado_por_user_id',
        'procesado_en',
    ];

    protected function casts(): array
    {
        return [
            'datos_solicitados' => 'array',
            'procesado_en' => 'datetime',
        ];
    }

    public function hermano(): BelongsTo
    {
        return $this->belongsTo(Hermano::class);
    }

    public function portalCuenta(): BelongsTo
    {
        return $this->belongsTo(HermanoPortalCuenta::class, 'hermano_portal_cuenta_id');
    }

    public function procesadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'procesado_por_user_id');
    }

    public function estaPendiente(): bool
    {
        return $this->estado === self::ESTADO_PENDIENTE;
    }
}
