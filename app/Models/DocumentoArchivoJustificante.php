<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentoArchivoJustificante extends Model
{
    protected $table = 'documentos_archivo_justificantes';

    protected $fillable = [
        'documento_padre_id',
        'documento_hijo_id',
    ];

    public function documentoPadre(): BelongsTo
    {
        return $this->belongsTo(DocumentoArchivo::class, 'documento_padre_id');
    }

    public function documentoHijo(): BelongsTo
    {
        return $this->belongsTo(DocumentoArchivo::class, 'documento_hijo_id');
    }
}
