<?php

namespace App\Http\Requests\Secretaria;

use Illuminate\Foundation\Http\FormRequest;

class RechazarSolicitudCambioDatosRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'motivo_rechazo' => ['required', 'string', 'min:5', 'max:2000'],
        ];
    }
}
