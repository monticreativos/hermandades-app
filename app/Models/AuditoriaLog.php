<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditoriaLog extends Model
{
    protected $table = 'auditoria_logs';

    protected $fillable = [
        'canal',
        'evento',
        'descripcion',
        'user_id',
        'hermano_portal_cuenta_id',
        'hermano_id',
        'email_intento',
        'ip_address',
        'user_agent',
        'metodo_http',
        'ruta',
        'path',
        'codigo_http',
        'payload',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function portalCuenta(): BelongsTo
    {
        return $this->belongsTo(HermanoPortalCuenta::class, 'hermano_portal_cuenta_id');
    }

    public function hermano(): BelongsTo
    {
        return $this->belongsTo(Hermano::class);
    }
}
