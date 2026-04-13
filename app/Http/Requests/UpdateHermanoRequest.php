<?php

namespace App\Http\Requests;

use App\Services\Contabilidad\CuotaPeriodicidadService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UpdateHermanoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->input('importe_cuota_anual_referencia') === '' || $this->input('importe_cuota_anual_referencia') === null) {
            $this->merge(['importe_cuota_anual_referencia' => null]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $hermanoId = (int) $this->route('hermano')->id;

        return [
            'numero_hermano' => ['nullable', 'integer', 'min:1', Rule::unique('hermanos', 'numero_hermano')->ignore($hermanoId)],
            'nombre' => ['required', 'string', 'max:120'],
            'apellidos' => ['required', 'string', 'max:180'],
            'dni' => ['required', 'string', 'max:12', Rule::unique('hermanos', 'dni')->ignore($hermanoId), function ($attribute, $value, $fail) {
                if (! $this->esDniValido((string) $value)) {
                    $fail('El DNI/NIE no tiene un formato válido.');
                }
            }],
            'fecha_nacimiento' => ['required', 'date', 'before:today'],
            'sexo' => ['required', Rule::in(['Hombre', 'Mujer', 'Otro'])],
            'direccion' => ['nullable', 'string', 'max:255'],
            'localidad' => ['nullable', 'string', 'max:100'],
            'provincia' => ['nullable', 'string', 'max:100'],
            'codigo_postal' => ['nullable', 'regex:/^\d{5}$/'],
            'telefono' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:150', Rule::unique('hermanos', 'email')->ignore($hermanoId)],
            'banco_id' => ['nullable', 'integer', 'exists:bancos,id'],
            'sucursal' => ['nullable', 'string', 'max:120'],
            'iban' => ['nullable', 'string', function ($attribute, $value, $fail) {
                if ($value !== null && $value !== '' && ! $this->esIbanValido((string) $value)) {
                    $fail('El IBAN no tiene un formato válido.');
                }
            }],
            'titular_cuenta' => ['nullable', 'string', 'max:180'],
            'titular_cuenta_menor' => ['nullable', 'string', 'max:180'],
            'periodicidad_pago' => ['nullable', Rule::in(CuotaPeriodicidadService::periodicidades())],
            'importe_cuota_anual_referencia' => ['nullable', 'numeric', 'min:0', 'max:99999.99'],
            'fecha_alta' => ['nullable', 'date'],
            'fecha_baja' => ['nullable', 'date', 'after_or_equal:fecha_alta'],
            'fecha_bautismo' => ['nullable', 'date', 'before_or_equal:today'],
            'parroquia_bautismo' => ['nullable', 'string', 'max:180'],
            'estado' => ['required', Rule::in(['Alta', 'Baja', 'Difunto'])],
            'observaciones' => ['nullable', 'string'],
            'partida_bautismo' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:4096'],
            'dni_escaneado' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:4096'],
        ];
    }

    private function esDniValido(string $dni): bool
    {
        $dni = strtoupper(trim($dni));
        $dni = str_replace([' ', '-'], '', $dni);

        if (preg_match('/^\d{8}[A-Z]$/', $dni) !== 1) {
            if (preg_match('/^[XYZ]\d{7}[A-Z]$/', $dni) !== 1) {
                return false;
            }
            $map = ['X' => '0', 'Y' => '1', 'Z' => '2'];
            $dni = $map[$dni[0]].substr($dni, 1);
        }

        $numero = (int) substr($dni, 0, 8);
        $letra = substr($dni, -1);
        $tabla = 'TRWAGMYFPDXBNJZSQVHLCKE';

        return $tabla[$numero % 23] === $letra;
    }

    private function esIbanValido(string $iban): bool
    {
        $iban = strtoupper(Str::replace(' ', '', $iban));
        if (preg_match('/^[A-Z]{2}\d{2}[A-Z0-9]{10,30}$/', $iban) !== 1) {
            return false;
        }

        $reordenado = substr($iban, 4).substr($iban, 0, 4);
        $numeric = '';
        foreach (str_split($reordenado) as $char) {
            $numeric .= ctype_alpha($char) ? (string) (ord($char) - 55) : $char;
        }

        $checksum = 0;
        foreach (str_split($numeric) as $digit) {
            $checksum = ($checksum * 10 + (int) $digit) % 97;
        }

        return $checksum === 1;
    }
}
