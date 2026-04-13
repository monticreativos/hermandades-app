<?php

namespace App\Http\Requests\Secretaria;

use App\Models\DocumentoArchivo;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreFirmaConformidadSolicitudRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'hermano_id' => ['required', 'integer', 'exists:hermanos,id'],
            'titulo' => ['required', 'string', 'max:255'],
            'descripcion' => ['required', 'string', 'max:10000'],
            'documento_archivo_id' => [
                'nullable',
                'integer',
                Rule::exists('documentos_archivo', 'id'),
            ],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $id = $this->input('documento_archivo_id');
            if ($id) {
                $doc = DocumentoArchivo::query()->find($id);
                if ($doc && $doc->nivel_acceso !== DocumentoArchivo::NIVEL_PUBLICO_HERMANOS) {
                    $validator->errors()->add(
                        'documento_archivo_id',
                        'Solo pueden enlazarse documentos con nivel «Público para hermanos» para el portal.'
                    );
                }
            }
        });
    }
}
