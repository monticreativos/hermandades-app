<?php

namespace App\Http\Requests\Economia;

use Illuminate\Foundation\Http\FormRequest;

class StoreRemesaSepaRequest extends FormRequest
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
            'año' => ['required', 'integer', 'min:2000', 'max:2100'],
            'mes' => ['required', 'integer', 'min:1', 'max:12'],
            'fecha_cobro' => ['required', 'date'],
        ];
    }
}
