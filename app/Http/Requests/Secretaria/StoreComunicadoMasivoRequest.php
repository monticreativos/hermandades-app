<?php

namespace App\Http\Requests\Secretaria;

use App\Models\ComunicadoMasivo;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreComunicadoMasivoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'asunto' => ['required', 'string', 'max:255'],
            'cuerpo_html' => ['required', 'string', 'max:65000'],
            'filtro_envio' => [
                'required',
                'string',
                Rule::in([
                    ComunicadoMasivo::FILTRO_TODOS,
                    ComunicadoMasivo::FILTRO_CON_DEUDA,
                    ComunicadoMasivo::FILTRO_TRAMO_COFRADIA,
                    ComunicadoMasivo::FILTRO_SOLO_COSTALEROS,
                    ComunicadoMasivo::FILTRO_CONTACTOS_EXTERNOS,
                    ComunicadoMasivo::FILTRO_AUDIENCIA_MIXTA,
                ]),
            ],
            'filtro_tramo_valor' => [
                'nullable',
                'string',
                'max:120',
                'required_if:filtro_envio,'.ComunicadoMasivo::FILTRO_TRAMO_COFRADIA,
            ],
            'filtro_contacto_categoria' => ['nullable', 'string', 'max:60'],
            'audiencia_mixta' => ['nullable', 'array'],
            'audiencia_mixta.*' => ['string', 'max:120'],
            'destinatarios_individuales' => ['nullable', 'array'],
            'destinatarios_individuales.*' => ['string', 'max:50'],
        ];
    }
}
