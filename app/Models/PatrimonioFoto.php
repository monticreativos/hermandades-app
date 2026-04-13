<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class PatrimonioFoto extends Model
{
    protected $table = 'patrimonio_fotos';

    protected $fillable = [
        'enser_id',
        'archivo_path',
        'leyenda',
        'tipo_foto',
        'orden',
    ];

    protected function casts(): array
    {
        return [
            'orden' => 'integer',
        ];
    }

    public function enser(): BelongsTo
    {
        return $this->belongsTo(Enser::class, 'enser_id');
    }

    public function url(): string
    {
        if (str_starts_with($this->archivo_path, 'http')) {
            return $this->archivo_path;
        }

        return Storage::url($this->archivo_path);
    }
}
