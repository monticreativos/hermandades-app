<?php

namespace App\Http\Requests\Tienda;

use App\Models\VentaTienda;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreVentaTpvRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'items' => ['required', 'array', 'min:1'],
            'items.*.producto_id' => ['required', 'integer', 'exists:productos_tienda,id'],
            'items.*.cantidad' => ['required', 'integer', 'min:1', 'max:9999'],
            'metodo_pago' => ['required', 'string', Rule::in([
                VentaTienda::METODO_EFECTIVO,
                VentaTienda::METODO_TARJETA,
                VentaTienda::METODO_BIZUM,
            ])],
            'venta_anonima' => ['sometimes', 'boolean'],
            'hermano_id' => ['nullable', 'integer', 'exists:hermanos,id'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'venta_anonima' => $this->boolean('venta_anonima'),
        ]);
    }
}
