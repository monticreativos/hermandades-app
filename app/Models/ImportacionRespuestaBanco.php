<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImportacionRespuestaBanco extends Model
{
    protected $table = 'importaciones_respuesta_banco';

    protected $fillable = [
        'remesa_id',
        'user_id',
        'tipo_archivo',
        'archivo_path',
        'nombre_original',
        'resultado_json',
        'recibos_cobrados',
        'recibos_devueltos',
        'recibos_no_encontrados',
    ];

    protected function casts(): array
    {
        return [
            'resultado_json' => 'array',
        ];
    }

    public function remesa(): BelongsTo
    {
        return $this->belongsTo(RemesaSepa::class, 'remesa_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
