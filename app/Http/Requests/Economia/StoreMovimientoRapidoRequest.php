<?php

namespace App\Http\Requests\Economia;

use App\Services\Contabilidad\CategoriaMovimientoEconomia;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreMovimientoRapidoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermissionTo('contabilidad.gestion') ?? false;
    }

    public function rules(): array
    {
        $cats = array_map(fn (CategoriaMovimientoEconomia $c) => $c->value, CategoriaMovimientoEconomia::cases());

        return [
            'categoria' => ['required', 'string', Rule::in($cats)],
            'fecha' => ['required', 'date'],
            'importe' => ['required', 'numeric', 'min:0.01', 'max:999999.99'],
            'metodo_tesoreria' => ['required', 'string', Rule::in(['caja', 'banco'])],
            'hermano_id' => ['nullable', 'integer', 'exists:hermanos,id'],
            'proveedor_id' => ['nullable', 'integer', 'exists:proveedores,id'],
            'apt_modelo_182' => ['sometimes', 'boolean'],
            'glosa' => ['nullable', 'string', 'max:500'],
            'proveedor_texto' => ['nullable', 'string', 'max:200'],
            'base_imponible' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
            'cuota_iva' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
            'archivo' => ['nullable', 'file', 'max:10240', 'mimes:pdf,jpg,jpeg,png,webp'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v): void {
            if ($v->errors()->isNotEmpty()) {
                return;
            }
            $cat = CategoriaMovimientoEconomia::tryFrom((string) $v->getValue('categoria'));
            if (! $cat) {
                return;
            }
            if ($cat === CategoriaMovimientoEconomia::IngresoCuota && blank($v->getValue('hermano_id'))) {
                $v->errors()->add('hermano_id', 'Seleccione el hermano que abona la cuota.');
            }
            if ($cat->esIngreso() && (bool) $v->getValue('apt_modelo_182') && $cat !== CategoriaMovimientoEconomia::IngresoDonativo) {
                $v->errors()->add('apt_modelo_182', 'La desgravación fiscal (modelo 182) solo aplica a donativos.');
            }
            if ($cat === CategoriaMovimientoEconomia::IngresoDonativo && (bool) $v->getValue('apt_modelo_182') && blank($v->getValue('hermano_id'))) {
                $v->errors()->add('hermano_id', 'Indique el donante para donativos con desgravación fiscal.');
            }
            if (! $cat->esIngreso() && $cat !== CategoriaMovimientoEconomia::PagoProveedor) {
                $base = $v->getValue('base_imponible');
                $cuota = $v->getValue('cuota_iva');
                $tieneBase = $base !== null && $base !== '' && (float) $base > 0;
                $tieneCuota = $cuota !== null && $cuota !== '' && (float) $cuota > 0.004;
                if ($tieneBase xor $tieneCuota) {
                    $v->errors()->add('base_imponible', 'Si desglosa IVA, indique base imponible e IVA soportado.');
                }
            }
            if ($cat === CategoriaMovimientoEconomia::PagoProveedor) {
                if (blank($v->getValue('proveedor_id'))) {
                    $v->errors()->add('proveedor_id', 'Seleccione el proveedor cuya deuda liquida.');
                }
                $base = $v->getValue('base_imponible');
                $cuota = $v->getValue('cuota_iva');
                $tieneBase = $base !== null && $base !== '' && (float) $base > 0;
                $tieneCuota = $cuota !== null && $cuota !== '' && (float) $cuota > 0.004;
                if ($tieneBase || $tieneCuota) {
                    $v->errors()->add('base_imponible', 'En «Pago a proveedor» no desglose IVA; deje base e IVA en blanco.');
                }
            }
        });
    }

    public function attributes(): array
    {
        return [
            'apt_modelo_182' => 'apto para desgravación fiscal (Ley de Mecenazgo / modelo 182)',
        ];
    }
}
