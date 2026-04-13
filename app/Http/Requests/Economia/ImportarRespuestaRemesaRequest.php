<?php

namespace App\Http\Requests\Economia;

use Illuminate\Foundation\Http\FormRequest;

class ImportarRespuestaRemesaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('contabilidad.gestion') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'archivo_respuesta' => ['required', 'file', 'max:5120', 'mimes:xml,txt,csv'],
        ];
    }
}
