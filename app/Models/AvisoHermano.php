<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AvisoHermano extends Model
{
    protected $table = 'aviso_hermano';

    protected $fillable = [
        'aviso_id',
        'hermano_id',
        'leido_en',
    ];

    protected function casts(): array
    {
        return [
            'leido_en' => 'datetime',
        ];
    }

    public function aviso(): BelongsTo
    {
        return $this->belongsTo(Aviso::class);
    }

    public function hermano(): BelongsTo
    {
        return $this->belongsTo(Hermano::class);
    }

    public function marcarLeido(): void
    {
        if ($this->leido_en === null) {
            $this->forceFill(['leido_en' => now()])->save();
        }
    }
}
