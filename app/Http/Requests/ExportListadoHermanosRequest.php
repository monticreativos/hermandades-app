<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ExportListadoHermanosRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $columnasPermitidas = [
            'numero_hermano',
            'nombre_completo',
            'telefono',
            'email',
            'antiguedad',
            'dni',
            'codigo_postal',
            'localidad',
            'estado',
        ];

        return [
            'estado' => ['nullable', Rule::in(['Alta', 'Baja', 'Difunto', 'todos'])],
            'columnas' => ['required', 'array', 'min:1'],
            'columnas.*' => ['string', Rule::in($columnasPermitidas)],
        ];
    }
}
