<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DocumentoArchivo extends Model
{
    protected $table = 'documentos_archivo';

    public const CATEGORIA_REGLAS = 'reglas_hermandad';

    public const CATEGORIA_ACTAS = 'actas_cabildo';

    public const CATEGORIA_INVENTARIO_ARTISTICO = 'inventario_artistico';

    public const CATEGORIA_BOLETIN = 'boletin';

    public const CATEGORIA_CONTRATOS = 'contratos';

    public const NIVEL_JUNTA = 'junta_gobierno';

    public const NIVEL_PUBLICO_HERMANOS = 'publico_hermanos';

    protected $fillable = [
        'titulo',
        'categoria',
        'nivel_acceso',
        'descripcion',
        'resumen_ia',
        'archivo_path',
        'nombre_original',
        'mime',
        'tamano_bytes',
        'subido_por_user_id',
    ];

    protected function casts(): array
    {
        return [
            'tamano_bytes' => 'integer',
        ];
    }

    public function subidoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'subido_por_user_id');
    }

    public function solicitudesFirma(): HasMany
    {
        return $this->hasMany(FirmaConformidadSolicitud::class, 'documento_archivo_id');
    }

    public function justificantes(): HasMany
    {
        return $this->hasMany(DocumentoArchivoJustificante::class, 'documento_padre_id');
    }

    /**
     * @return array<string, string>
     */
    public static function etiquetasCategoria(): array
    {
        return [
            self::CATEGORIA_REGLAS => 'Reglas de la Hermandad',
            self::CATEGORIA_ACTAS => 'Actas de Cabildo',
            self::CATEGORIA_INVENTARIO_ARTISTICO => 'Inventario artístico',
            self::CATEGORIA_BOLETIN => 'Boletín',
            self::CATEGORIA_CONTRATOS => 'Contratos',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function etiquetasNivel(): array
    {
        return [
            self::NIVEL_JUNTA => 'Solo Junta de Gobierno',
            self::NIVEL_PUBLICO_HERMANOS => 'Público para hermanos (portal)',
        ];
    }
}
