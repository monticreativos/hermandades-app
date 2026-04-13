<?php

namespace App\Http\Requests\Portal;

use Illuminate\Foundation\Http\FormRequest;

class StorePortalBizumCuotaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('portal')->check();
    }

    public function rules(): array
    {
        return [
            'importe' => ['required', 'numeric', 'min:0.01', 'max:99999.99'],
            'confirmar' => ['accepted'],
        ];
    }

    public function attributes(): array
    {
        return [
            'confirmar' => 'confirmación de pago simulado',
        ];
    }
}
