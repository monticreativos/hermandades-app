<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FirmaConformidadSolicitud extends Model
{
    protected $table = 'firma_conformidad_solicitudes';

    public const ESTADO_PENDIENTE = 'pendiente';

    public const ESTADO_FIRMADO = 'firmado';

    protected $fillable = [
        'titulo',
        'descripcion',
        'documento_archivo_id',
        'hermano_id',
        'estado',
        'firmado_en',
        'firmado_ip',
        'creado_por_user_id',
    ];

    protected function casts(): array
    {
        return [
            'firmado_en' => 'datetime',
        ];
    }

    public function documentoArchivo(): BelongsTo
    {
        return $this->belongsTo(DocumentoArchivo::class, 'documento_archivo_id');
    }

    public function hermano(): BelongsTo
    {
        return $this->belongsTo(Hermano::class);
    }

    public function creadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creado_por_user_id');
    }
}
