<?php

namespace App\Http\Requests\Tienda;

use Illuminate\Foundation\Http\FormRequest;

class StoreCierreCajaTiendaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'fecha' => ['required', 'date'],
            'conteo_efectivo_fisico' => ['required', 'numeric', 'min:0', 'max:9999999.99'],
            'notas' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
