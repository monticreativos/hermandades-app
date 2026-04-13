<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InformeHistorial extends Model
{
    public const TIPO_ARQUEO_TESORERIA_MENSUAL = 'arqueo_tesoreria_mensual';

    protected $table = 'informes_historial';

    protected $fillable = [
        'tipo',
        'titulo',
        'periodo_año',
        'periodo_mes',
        'archivo_path',
        'user_id',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
        ];
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
