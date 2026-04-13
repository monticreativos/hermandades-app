<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Insignia extends Model
{
    public const TRAMO_CRISTO = 'Cristo';

    public const TRAMO_VIRGEN = 'Virgen';

    public const TRAMO_GENERAL = 'General';

    protected $fillable = [
        'nombre',
        'tramo',
        'orden',
        'max_portadores',
        'max_acompanantes',
        'notas',
    ];

    protected $casts = [
        'orden' => 'integer',
        'max_portadores' => 'integer',
        'max_acompanantes' => 'integer',
    ];

    public function papeletas(): HasMany
    {
        return $this->hasMany(PapeletaSitio::class);
    }
}
