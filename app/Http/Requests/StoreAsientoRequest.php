<?php

namespace App\Http\Requests;

use App\Models\CuentaContable;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreAsientoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermissionTo('contabilidad.gestion') ?? false;
    }

    public function rules(): array
    {
        return [
            'fecha' => ['required', 'date'],
            'glosa' => ['required', 'string', 'max:500'],
            'apuntes' => ['required', 'array', 'min:2'],
            'apuntes.*.cuenta_contable_id' => ['required', 'integer', 'exists:cuentas_contables,id'],
            'apuntes.*.debe' => ['required', 'numeric', 'min:0', 'decimal:0,2'],
            'apuntes.*.haber' => ['required', 'numeric', 'min:0', 'decimal:0,2'],
            'apuntes.*.concepto_detalle' => ['nullable', 'string', 'max:500'],
            'apuntes.*.archivo_factura' => ['nullable', 'file', 'max:10240', 'mimes:pdf,jpeg,jpg,png,webp'],
            'apuntes.*.factura_proveedor' => ['nullable', 'string', 'max:255'],
            'apuntes.*.factura_estado' => ['nullable', 'string', 'in:Pendiente,Pagada'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v): void {
            $apuntes = $this->input('apuntes', []);
            if (! is_array($apuntes) || count($apuntes) < 2) {
                return;
            }

            $totalDebe = '0';
            $totalHaber = '0';

            foreach ($apuntes as $i => $row) {
                if (! is_array($row)) {
                    continue;
                }
                $debe = (string) ($row['debe'] ?? '0');
                $haber = (string) ($row['haber'] ?? '0');
                $d = (float) $debe;
                $h = (float) $haber;

                if ($d <= 0 && $h <= 0) {
                    $v->errors()->add('apuntes.'.$i.'.debe', 'Cada línea debe tener importe en debe o en haber.');
                }
                if ($d > 0 && $h > 0) {
                    $v->errors()->add('apuntes.'.$i.'.debe', 'No puede haber importe en debe y haber a la vez en la misma línea.');
                }

                $totalDebe = bcadd($totalDebe, number_format($d, 2, '.', ''), 2);
                $totalHaber = bcadd($totalHaber, number_format($h, 2, '.', ''), 2);
            }

            if (bccomp($totalDebe, $totalHaber, 2) !== 0) {
                $v->errors()->add('apuntes', 'El asiento no cuadra: la suma del debe ('.$totalDebe.' €) debe coincidir con la del haber ('.$totalHaber.' €).');
            }

            $idsCuenta = [];
            foreach ($apuntes as $row) {
                if (is_array($row) && ! empty($row['cuenta_contable_id'])) {
                    $idsCuenta[] = (int) $row['cuenta_contable_id'];
                }
            }
            $cuentas = CuentaContable::query()->whereIn('id', array_unique($idsCuenta))->get()->keyBy('id');

            foreach ($apuntes as $i => $row) {
                if (! is_array($row)) {
                    continue;
                }
                $file = $this->file('apuntes.'.$i.'.archivo_factura');
                if (! $file || ! $file->isValid()) {
                    continue;
                }
                $cid = (int) ($row['cuenta_contable_id'] ?? 0);
                $cuenta = $cuentas->get($cid);
                $debe = (float) ($row['debe'] ?? 0);
                if (! $cuenta || $cuenta->tipo !== 'Gasto' || $debe <= 0) {
                    $v->errors()->add('apuntes.'.$i.'.archivo_factura', 'La factura solo puede adjuntarse en líneas de gasto (grupo 6) con importe al debe.');
                }
            }
        });
    }
}
