<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ComunicadoMasivoDestinatario extends Model
{
    protected $table = 'comunicado_masivo_destinatarios';

    protected $fillable = [
        'comunicado_masivo_id',
        'hermano_id',
        'contacto_externo_id',
        'tracking_token',
        'nombre_destinatario',
        'email_destinatario',
        'correo_enviado_en',
        'abierto_en',
        'aperturas_count',
        'ultima_apertura_ip',
    ];

    protected function casts(): array
    {
        return [
            'correo_enviado_en' => 'datetime',
            'abierto_en' => 'datetime',
        ];
    }

    public function comunicadoMasivo(): BelongsTo
    {
        return $this->belongsTo(ComunicadoMasivo::class);
    }

    public function hermano(): BelongsTo
    {
        return $this->belongsTo(Hermano::class);
    }

    public function contactoExterno(): BelongsTo
    {
        return $this->belongsTo(ContactoExterno::class, 'contacto_externo_id');
    }

    public function registrarApertura(?string $ip): void
    {
        $this->aperturas_count = (int) $this->aperturas_count + 1;
        $this->ultima_apertura_ip = $ip;
        if ($this->abierto_en === null) {
            $this->abierto_en = now();
        }
        $this->save();
    }
}
