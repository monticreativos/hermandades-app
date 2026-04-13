<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class ElementoPatrimonio extends Model
{
    protected $table = 'enseres';

    protected $fillable = [
        'numero_inventario',
        'codigo_qr_uuid',
        'nombre',
        'categoria_id',
        'ubicacion',
        'tipo_ubicacion',
        'autor',
        'año_creacion',
        'materiales',
        'material_tecnica',
        'dimensiones',
        'estado_conservacion_id',
        'valor_estimado',
        'valor_historico_artistico',
        'descripcion_detallada',
        'ultima_revision',
        'imagen_principal_path',
    ];

    protected $casts = [
        'ultima_revision' => 'date',
        'valor_estimado' => 'decimal:2',
    ];

    public function categoriaPatrimonio(): BelongsTo
    {
        return $this->belongsTo(CategoriaPatrimonio::class, 'categoria_id');
    }

    public function estadoConservacionPatrimonio(): BelongsTo
    {
        return $this->belongsTo(EstadoConservacionPatrimonio::class, 'estado_conservacion_id');
    }

    public function fotos(): HasMany
    {
        return $this->hasMany(PatrimonioFoto::class, 'enser_id')->orderBy('orden');
    }

    public function urlImagenPrincipal(): ?string
    {
        if (! $this->imagen_principal_path) {
            return null;
        }

        if (str_starts_with($this->imagen_principal_path, 'http')) {
            return $this->imagen_principal_path;
        }

        return Storage::url($this->imagen_principal_path);
    }
}
