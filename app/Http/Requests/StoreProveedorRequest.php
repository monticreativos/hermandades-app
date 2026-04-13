<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProveedorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    protected function prepareForValidation(): void
    {
        $nif = $this->input('nif_cif');
        if (is_string($nif) && trim($nif) === '') {
            $this->merge(['nif_cif' => null]);
        }
    }

    public function rules(): array
    {
        return [
            'razon_social' => ['required', 'string', 'max:255'],
            'nombre_comercial' => ['nullable', 'string', 'max:255'],
            'tipo_persona' => ['required', 'string', Rule::in(['juridica', 'fisica', 'autonomo'])],
            'nif_cif' => ['nullable', 'string', 'max:32', Rule::unique('proveedores', 'nif_cif')],
            'direccion' => ['nullable', 'string', 'max:255'],
            'codigo_postal' => ['nullable', 'string', 'max:16'],
            'municipio' => ['nullable', 'string', 'max:120'],
            'provincia' => ['nullable', 'string', 'max:120'],
            'pais' => ['nullable', 'string', 'size:2'],
            'telefono' => ['nullable', 'string', 'max:32'],
            'email' => ['nullable', 'email', 'max:255'],
            'regimen_iva' => ['nullable', 'string', Rule::in([
                'general', 'recargo_equivalencia', 'exento', 'no_sujeto', 'intracomunitario', 'otros',
            ])],
            'iban' => ['nullable', 'string', 'max:34'],
            'notas' => ['nullable', 'string', 'max:5000'],
        ];
    }

    public function attributes(): array
    {
        return [
            'razon_social' => 'razón social',
            'nombre_comercial' => 'nombre comercial',
            'tipo_persona' => 'tipo de persona',
            'nif_cif' => 'NIF/CIF/NIE',
            'codigo_postal' => 'código postal',
            'regimen_iva' => 'régimen de IVA',
        ];
    }
}
