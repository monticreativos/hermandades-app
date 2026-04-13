<?php

namespace App\Http\Requests\Tienda;

use App\Models\ProductoTienda;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductoTiendaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:255'],
            'categoria' => ['required', 'string', Rule::in(ProductoTienda::categoriasValores())],
            'precio_venta' => ['required', 'numeric', 'min:0', 'max:999999.99'],
            'precio_coste' => ['required', 'numeric', 'min:0', 'max:999999.99'],
            'iva_porcentaje' => ['required', 'numeric', 'min:0', 'max:100'],
            'stock_actual' => ['required', 'integer', 'min:0', 'max:9999999'],
            'stock_minimo' => ['required', 'integer', 'min:0', 'max:9999999'],
            'sku' => ['nullable', 'string', 'max:64', 'unique:productos_tienda,sku'],
            'imagenes' => ['nullable', 'array', 'max:10'],
            'imagenes.*' => ['image', 'max:4096'],
            'activo' => ['sometimes', 'boolean'],
        ];
    }
}
