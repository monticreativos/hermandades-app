<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ComunicadoMasivo extends Model
{
    protected $table = 'comunicados_masivos';

    public const FILTRO_TODOS = 'todos';

    public const FILTRO_CON_DEUDA = 'con_deuda';

    public const FILTRO_TRAMO_COFRADIA = 'tramo_cofradia';

    public const FILTRO_SOLO_COSTALEROS = 'solo_costaleros';
    public const FILTRO_CONTACTOS_EXTERNOS = 'contactos_externos';
    public const FILTRO_AUDIENCIA_MIXTA = 'audiencia_mixta';

    public const ESTADO_ENCOLADO = 'encolado';

    public const ESTADO_ENVIANDO = 'enviando';

    public const ESTADO_COMPLETADO = 'completado';

    public const ESTADO_ERROR = 'error';

    protected $fillable = [
        'asunto',
        'cuerpo_html',
        'filtro_envio',
        'filtro_tramo_valor',
        'filtro_contacto_categoria',
        'audiencia_mixta',
        'destinatarios_individuales',
        'creado_por_user_id',
        'estado',
        'total_destinatarios',
        'correos_enviados',
        'finalizado_en',
    ];

    protected function casts(): array
    {
        return [
            'finalizado_en' => 'datetime',
            'audiencia_mixta' => 'array',
            'destinatarios_individuales' => 'array',
        ];
    }

    public function creadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creado_por_user_id');
    }

    public function destinatarios(): HasMany
    {
        return $this->hasMany(ComunicadoMasivoDestinatario::class);
    }
}
