<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class DocumentoGasto extends Model
{
    public const ESTADO_PENDIENTE = 'Pendiente';

    public const ESTADO_PAGADA = 'Pagada';

    protected $table = 'documentos_gasto';

    protected $fillable = [
        'asiento_id',
        'proveedor_id',
        'orden_linea',
        'archivo_path',
        'nombre_original',
        'mime_type',
        'proveedor',
        'estado',
        'importe_linea',
        'fecha_documento',
    ];

    protected $casts = [
        'importe_linea' => 'decimal:2',
        'fecha_documento' => 'date',
    ];

    protected static function booted(): void
    {
        static::deleting(function (DocumentoGasto $documentoGasto): void {
            if ($documentoGasto->archivo_path && Storage::disk('local')->exists($documentoGasto->archivo_path)) {
                Storage::disk('local')->delete($documentoGasto->archivo_path);
            }
        });
    }

    public function asiento(): BelongsTo
    {
        return $this->belongsTo(Asiento::class);
    }

    public function proveedorRegistrado(): BelongsTo
    {
        return $this->belongsTo(Proveedor::class, 'proveedor_id');
    }

    public function nombreProveedorMostrar(): string
    {
        $r = $this->proveedorRegistrado;
        if ($r) {
            return $r->razon_social;
        }

        return $this->proveedor ?: 'Sin proveedor';
    }
}
