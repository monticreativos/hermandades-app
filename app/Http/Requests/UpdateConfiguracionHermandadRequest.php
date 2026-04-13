<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateConfiguracionHermandadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre_hermandad' => ['required', 'string', 'max:255'],
            'nombre_corto' => ['nullable', 'string', 'max:120'],
            'cif' => ['nullable', 'string', 'max:20'],
            'direccion' => ['nullable', 'string', 'max:255'],
            'localidad' => ['nullable', 'string', 'max:120'],
            'cp' => ['nullable', 'string', 'max:10'],
            'provincia' => ['nullable', 'string', 'max:120'],
            'telefono' => ['nullable', 'string', 'max:30'],
            'email_contacto' => ['nullable', 'email', 'max:255'],
            'iban_cuotas' => ['nullable', 'string', 'max:34'],
            'bic_swift' => ['nullable', 'string', 'max:20'],
            'escudo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'firma_secretario' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'firma_mayordomo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'sello_hermandad' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'censo_antiguedad_anos' => ['nullable', 'integer', 'min:0', 'max:80'],
            'importe_cuota_anual_defecto' => ['nullable', 'numeric', 'min:0', 'max:99999.99'],
        ];
    }
}
