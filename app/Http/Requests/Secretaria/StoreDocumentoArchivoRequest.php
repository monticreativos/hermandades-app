<?php

namespace App\Http\Requests\Secretaria;

use App\Models\DocumentoArchivo;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDocumentoArchivoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'titulo' => ['required', 'string', 'max:255'],
            'categoria' => ['required', 'string', Rule::in(array_keys(DocumentoArchivo::etiquetasCategoria()))],
            'nivel_acceso' => ['required', 'string', Rule::in(array_keys(DocumentoArchivo::etiquetasNivel()))],
            'descripcion' => ['nullable', 'string', 'max:2000'],
            'archivo' => ['required', 'file', 'max:51200'],
        ];
    }
}
