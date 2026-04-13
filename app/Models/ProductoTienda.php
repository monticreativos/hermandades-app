<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Schema;

class ProductoTienda extends Model
{
    protected $table = 'productos_tienda';

    public const CATEGORIA_MEDALLAS = 'Medallas';

    public const CATEGORIA_LIBROS = 'Libros';

    public const CATEGORIA_ROPA = 'Ropa';

    public const CATEGORIA_INCIENSO = 'Incienso';

    public const CATEGORIA_VARIOS = 'Varios';

    protected $fillable = [
        'nombre',
        'categoria',
        'precio_venta',
        'precio_coste',
        'iva_porcentaje',
        'stock_actual',
        'stock_minimo',
        'sku',
        'imagen_path',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'precio_venta' => 'decimal:2',
            'precio_coste' => 'decimal:2',
            'iva_porcentaje' => 'decimal:2',
            'activo' => 'boolean',
        ];
    }

    public function lineasVenta(): HasMany
    {
        return $this->hasMany(VentaTiendaLinea::class, 'producto_tienda_id');
    }

    public function imagenes(): HasMany
    {
        return $this->hasMany(ProductoTiendaImagen::class, 'producto_tienda_id')
            ->orderBy('es_principal', 'desc')
            ->orderBy('orden');
    }

    /**
     * Precio TTC unitario → base + IVA (cuota).
     *
     * @return array{base: float, iva: float}
     */
    public static function desglosarPrecioTtc(float $precioUnitarioTtc, float $ivaPorcentaje): array
    {
        return self::desglosarLineaTtc(round($precioUnitarioTtc, 2), $ivaPorcentaje);
    }

    /**
     * Desglose de un importe total TTC (p. ej. línea = precio unitario × cantidad).
     *
     * @return array{base: float, iva: float}
     */
    public static function desglosarLineaTtc(float $totalLineaTtc, float $ivaPorcentaje): array
    {
        $ttc = round($totalLineaTtc, 2);
        if ($ivaPorcentaje <= 0) {
            return ['base' => $ttc, 'iva' => 0.0];
        }
        $base = round($ttc / (1 + $ivaPorcentaje / 100), 2);
        $iva = round($ttc - $base, 2);

        return ['base' => $base, 'iva' => $iva];
    }

    public static function categorias(): array
    {
        if (Schema::hasTable('categorias_tienda')) {
            $cats = CategoriaTienda::query()
                ->where('activa', true)
                ->orderBy('orden')
                ->orderBy('nombre')
                ->pluck('nombre', 'nombre')
                ->all();
            if ($cats !== []) {
                return $cats;
            }
        }

        return [self::CATEGORIA_MEDALLAS => 'Medallas', self::CATEGORIA_INCIENSO => 'Incienso', self::CATEGORIA_LIBROS => 'Libros', self::CATEGORIA_ROPA => 'Ropa', self::CATEGORIA_VARIOS => 'Varios'];
    }

    /**
     * @return list<string>
     */
    public static function categoriasValores(): array
    {
        return array_values(array_keys(self::categorias()));
    }

    public function bajoMinimo(): bool
    {
        return $this->stock_actual <= $this->stock_minimo;
    }

    public function urlImagen(): ?string
    {
        $principal = $this->relationLoaded('imagenes')
            ? $this->imagenes->first()
            : $this->imagenes()->first();
        if ($principal?->archivo_path) {
            return asset('storage/'.$principal->archivo_path);
        }

        if (! $this->imagen_path) {
            return null;
        }

        return asset('storage/'.$this->imagen_path);
    }
}
