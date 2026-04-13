<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GenerarCuotasAsientoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermissionTo('contabilidad.gestion') ?? false;
    }

    public function rules(): array
    {
        return [
            'grupo' => ['required', Rule::in(['todos_alta', 'todos'])],
            'importe' => ['required', 'numeric', 'min:0.01', 'decimal:0,2'],
            'glosa' => ['required', 'string', 'max:500'],
            'cuenta_debe_id' => ['nullable', 'integer', 'exists:cuentas_contables,id'],
            'cuenta_haber_id' => ['required', 'integer', 'exists:cuentas_contables,id'],
        ];
    }
}
