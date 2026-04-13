<?php

namespace App\Http\Requests\Portal;

use Illuminate\Foundation\Http\FormRequest;

class AceptarRgpdPortalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('portal')->check();
    }

    public function rules(): array
    {
        return [
            'acepto_rgpd' => ['accepted'],
        ];
    }

    public function attributes(): array
    {
        return [
            'acepto_rgpd' => 'cláusula de protección de datos',
        ];
    }
}
