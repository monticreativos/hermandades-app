<?php

namespace App\Http\Requests\Portal;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RestablecerContrasenaCodigoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'codigo' => ['required', 'string', 'size:6', 'regex:/^[0-9]{6}$/'],
            'password' => ['required', 'confirmed', Password::min(10)->mixedCase()->numbers()],
        ];
    }
}
