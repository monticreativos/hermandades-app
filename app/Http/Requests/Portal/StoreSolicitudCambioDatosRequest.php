<?php

namespace App\Http\Requests\Portal;

use App\Models\Hermano;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreSolicitudCambioDatosRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('portal')->check();
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('iban') && is_string($this->iban)) {
            $this->merge(['iban' => str_replace(' ', '', strtoupper(trim($this->iban)))]);
        }
    }

    public function rules(): array
    {
        return [
            'direccion' => ['nullable', 'string', 'max:500'],
            'localidad' => ['nullable', 'string', 'max:120'],
            'provincia' => ['nullable', 'string', 'max:120'],
            'codigo_postal' => ['nullable', 'string', 'max:16'],
            'telefono' => ['nullable', 'string', 'max:40'],
            'email' => ['nullable', 'email:rfc', 'max:255'],
            'iban' => ['nullable', 'string', 'max:34', 'regex:/^[A-Za-z]{2}[0-9]{2}[A-Za-z0-9]+$/'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v): void {
            if ($v->errors()->isNotEmpty()) {
                return;
            }
            $hermano = auth('portal')->user()?->hermano;
            if (! $hermano) {
                $v->errors()->add('direccion', 'No se pudo cargar su ficha de hermano.');

                return;
            }
            $cambios = $this->cambiosRespectoHermano($hermano);
            if ($cambios === []) {
                $v->errors()->add('direccion', 'Indique al menos un dato distinto al registrado actualmente.');
            }
        });
    }

    /**
     * @return array<string, array{antes: mixed, despues: mixed}>
     */
    public function cambiosRespectoHermano(Hermano $hermano): array
    {
        $campos = ['direccion', 'localidad', 'provincia', 'codigo_postal', 'telefono', 'email', 'iban'];
        $cambios = [];

        foreach ($campos as $campo) {
            if (! $this->has($campo)) {
                continue;
            }
            $nuevo = $this->input($campo);
            if ($nuevo === null || $nuevo === '') {
                continue;
            }
            $nuevo = is_string($nuevo) ? trim($nuevo) : $nuevo;
            $antes = $hermano->{$campo};
            $antesStr = $antes === null ? '' : (is_string($antes) ? trim($antes) : (string) $antes);
            if (strtoupper(str_replace(' ', '', $antesStr)) !== strtoupper(str_replace(' ', '', (string) $nuevo))) {
                $cambios[$campo] = ['antes' => $antesStr !== '' ? $antesStr : null, 'despues' => $nuevo];
            }
        }

        return $cambios;
    }
}
