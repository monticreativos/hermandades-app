<?php

namespace App\Http\Requests\Portal;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class EstablecerContrasenaPorCodigoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('codigo') && is_string($this->codigo)) {
            $this->merge(['codigo' => preg_replace('/\D/', '', $this->codigo)]);
        }
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email:rfc', 'max:255'],
            'codigo' => ['required', 'string', 'size:6', 'regex:/^[0-9]{6}$/'],
            'password' => ['required', 'confirmed', Password::min(10)->mixedCase()->numbers()],
        ];
    }
}
