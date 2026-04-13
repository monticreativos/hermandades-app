<?php

namespace App\Http\Requests\Secretaria;

use App\Models\Aviso;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAvisoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'titulo' => ['required', 'string', 'max:255'],
            'cuerpo' => ['required', 'string', 'max:10000'],
            'alcance' => ['required', 'string', Rule::in([
                Aviso::ALCANCE_MASIVO,
                Aviso::ALCANCE_INDIVIDUAL,
                Aviso::ALCANCE_SELECTIVO,
            ])],
            'hermano_id' => [
                'required_if:alcance,'.Aviso::ALCANCE_INDIVIDUAL,
                'nullable',
                'integer',
                'exists:hermanos,id',
            ],
            'hermano_ids' => [
                'required_if:alcance,'.Aviso::ALCANCE_SELECTIVO,
                'nullable',
                'array',
                'min:1',
            ],
            'hermano_ids.*' => ['integer', 'exists:hermanos,id'],
            'solo_alta' => ['sometimes', 'boolean'],
            'solo_portal' => ['sometimes', 'boolean'],
            'notificar_email' => ['sometimes', 'boolean'],
            'urgente' => ['sometimes', 'boolean'],
            'visible_tablon' => ['sometimes', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $alcance = $this->input('alcance');
        if ($alcance === Aviso::ALCANCE_MASIVO) {
            $this->merge([
                'solo_alta' => $this->has('solo_alta') ? $this->boolean('solo_alta') : true,
                'solo_portal' => $this->boolean('solo_portal'),
            ]);
        } else {
            $this->merge([
                'solo_alta' => true,
                'solo_portal' => false,
            ]);
        }

        $this->merge([
            'notificar_email' => $this->boolean('notificar_email'),
            'urgente' => $this->boolean('urgente'),
            'visible_tablon' => $this->has('visible_tablon') ? $this->boolean('visible_tablon') : true,
        ]);
    }
}
