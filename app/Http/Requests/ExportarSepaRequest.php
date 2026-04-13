<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ExportarSepaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermissionTo('cuotas.gestion') ?? false;
    }

    public function rules(): array
    {
        return [
            'grupo' => ['required', Rule::in(['todos_alta', 'todos'])],
            'importe' => ['required', 'numeric', 'min:0.01', 'decimal:0,2'],
            'concepto' => ['required', 'string', 'max:140'],
            'fecha_cobro' => ['nullable', 'date'],
        ];
    }
}
