<?php

namespace App\Http\Requests\Tienda;

use App\Models\VentaTienda;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CheckoutPedidoTpvRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'pedido_uuid' => ['required', 'uuid', 'exists:pedidos_tienda_portal,uuid'],
            'metodo_pago' => ['required', 'string', Rule::in([
                VentaTienda::METODO_EFECTIVO,
                VentaTienda::METODO_TARJETA,
                VentaTienda::METODO_BIZUM,
            ])],
        ];
    }
}
