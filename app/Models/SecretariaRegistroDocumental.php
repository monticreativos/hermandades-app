<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SecretariaRegistroDocumental extends Model
{
    protected $table = 'secretaria_registros_documentales';

    public const TIPO_ENTRADA = 'entrada';

    public const TIPO_SALIDA = 'salida';

    protected $fillable = [
        'fecha',
        'tipo_movimiento',
        'remitente_destinatario',
        'hermano_relacionado_id',
        'contacto_externo_id',
        'remitente_hermano_id',
        'remitente_proveedor_id',
        'remitente_contacto_externo_id',
        'destinatario_hermano_id',
        'destinatario_proveedor_id',
        'destinatario_contacto_externo_id',
        'extracto',
        'tipo_documento',
        'numero_protocolo',
        'archivo_path',
        'nombre_original',
        'mime',
        'tamano_bytes',
        'sello_registro_path',
        'resumen_ia',
        'subido_por_user_id',
    ];

    protected function casts(): array
    {
        return [
            'fecha' => 'date',
            'tamano_bytes' => 'integer',
        ];
    }

    public function subidoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'subido_por_user_id');
    }

    public function hermanoRelacionado(): BelongsTo
    {
        return $this->belongsTo(Hermano::class, 'hermano_relacionado_id');
    }

    public function contactoExterno(): BelongsTo
    {
        return $this->belongsTo(ContactoExterno::class, 'contacto_externo_id');
    }

    public function remitenteHermano(): BelongsTo
    {
        return $this->belongsTo(Hermano::class, 'remitente_hermano_id');
    }

    public function remitenteProveedor(): BelongsTo
    {
        return $this->belongsTo(Proveedor::class, 'remitente_proveedor_id');
    }

    public function remitenteContactoExterno(): BelongsTo
    {
        return $this->belongsTo(ContactoExterno::class, 'remitente_contacto_externo_id');
    }

    public function destinatarioHermano(): BelongsTo
    {
        return $this->belongsTo(Hermano::class, 'destinatario_hermano_id');
    }

    public function destinatarioProveedor(): BelongsTo
    {
        return $this->belongsTo(Proveedor::class, 'destinatario_proveedor_id');
    }

    public function destinatarioContactoExterno(): BelongsTo
    {
        return $this->belongsTo(ContactoExterno::class, 'destinatario_contacto_externo_id');
    }
}
