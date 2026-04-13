<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RecalcularNumerosHermanosRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAnyRole(['Administrador Hermandad', 'SuperAdmin']) ?? false;
    }

    public function rules(): array
    {
        return [
            'confirmacion' => ['required', 'string', Rule::in(['REORDENAR'])],
        ];
    }
}
