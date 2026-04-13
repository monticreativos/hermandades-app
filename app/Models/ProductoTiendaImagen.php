<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductoTiendaImagen extends Model
{
    protected $table = 'producto_tienda_imagenes';

    protected $fillable = [
        'producto_tienda_id',
        'archivo_path',
        'orden',
        'es_principal',
    ];

    protected function casts(): array
    {
        return [
            'orden' => 'integer',
            'es_principal' => 'boolean',
        ];
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(ProductoTienda::class, 'producto_tienda_id');
    }

    public function url(): string
    {
        return asset('storage/'.$this->archivo_path);
    }
}
