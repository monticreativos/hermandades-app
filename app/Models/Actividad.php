<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Actividad extends Model
{
    public const ACCION_ALTA_HERMANO = 'alta_hermano';

    public const ACCION_ELIMINAR_HERMANO = 'eliminar_hermano';

    public const ACCION_ELIMINAR_ASIENTO = 'eliminar_asiento';

    public const ACCION_CENSO_PDF = 'censo_pdf';

    public const ACCION_CAMBIO_ESCUDO = 'cambio_escudo';

    public const ACCION_RENUMERAR_HERMANOS = 'renumerar_hermanos';

    public const ACCION_SINCRONIZAR_CUENTAS_AUXILIARES = 'sincronizar_cuentas_auxiliares';

    protected $table = 'actividades';

    protected $fillable = [
        'user_id',
        'accion',
        'descripcion',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
