<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CostaleroPerfil extends Model
{
    protected $table = 'costalero_perfiles';

    public const PALO_COSTERO_IZQ = 'costero_izquierdo';

    public const PALO_COSTERO_DER = 'costero_derecho';

    public const PALO_FIJADOR = 'fijador';

    public const PALO_CORRIENTE = 'corriente';

    protected $fillable = [
        'hermano_id',
        'cuadrilla_id',
        'altura_cm',
        'calzado_talla',
        'ropa_talla',
        'trabajadera_numero',
        'palo',
        'anios_cuadrilla',
        'alergias',
        'lesiones',
    ];

    protected function casts(): array
    {
        return [
            'altura_cm' => 'integer',
            'calzado_talla' => 'integer',
            'trabajadera_numero' => 'integer',
            'anios_cuadrilla' => 'integer',
        ];
    }

    public function hermano(): BelongsTo
    {
        return $this->belongsTo(Hermano::class);
    }

    public function cuadrilla(): BelongsTo
    {
        return $this->belongsTo(Cuadrilla::class);
    }

    public static function palos(): array
    {
        return [
            self::PALO_COSTERO_IZQ => 'Costero Izquierdo',
            self::PALO_COSTERO_DER => 'Costero Derecho',
            self::PALO_FIJADOR => 'Fijador',
            self::PALO_CORRIENTE => 'Corriente',
        ];
    }
}
