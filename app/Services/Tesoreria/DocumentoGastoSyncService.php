<?php

namespace App\Services\Tesoreria;

use App\Models\Asiento;
use App\Models\CuentaContable;
use App\Models\DocumentoGasto;
use App\Models\Proveedor;
use Illuminate\Http\Request;

class DocumentoGastoSyncService
{
    /**
     * Alinea documentos con las líneas del asiento: subidas nuevas, actualización de metadatos y bajas si la línea ya no es gasto al debe.
     *
     * @param  array<int, array{cuenta_contable_id: int, debe: float, haber: float, concepto_detalle?: string|null}>  $lineas
     */
    public function sincronizar(Asiento $asiento, Request $request, array $lineas): void
    {
        $idsCuenta = array_values(array_unique(array_filter(array_column($lineas, 'cuenta_contable_id'))));
        $cuentas = CuentaContable::query()->whereIn('id', $idsCuenta)->get()->keyBy('id');

        $n = count($lineas);
        DocumentoGasto::query()
            ->where('asiento_id', $asiento->id)
            ->where('orden_linea', '>=', $n)
            ->get()
            ->each(fn (DocumentoGasto $d) => $d->delete());

        foreach ($lineas as $idx => $linea) {
            $cuenta = $cuentas->get($linea['cuenta_contable_id']);
            $esGastoDebe = $cuenta && $cuenta->tipo === 'Gasto' && (float) $linea['debe'] > 0;

            $existing = DocumentoGasto::query()
                ->where('asiento_id', $asiento->id)
                ->where('orden_linea', $idx)
                ->first();

            if (! $esGastoDebe) {
                if ($existing) {
                    $existing->delete();
                }

                continue;
            }

            $file = $request->file('apuntes.'.$idx.'.archivo_factura');
            if ($file && $file->isValid()) {
                if ($existing) {
                    $existing->delete();
                }
                $path = $file->store("gastos/{$asiento->id}", 'local');
                DocumentoGasto::query()->create([
                    'asiento_id' => $asiento->id,
                    'orden_linea' => $idx,
                    'archivo_path' => $path,
                    'nombre_original' => $file->getClientOriginalName(),
                    'mime_type' => $file->getClientMimeType(),
                    'proveedor' => $this->trimOrNull($request->input("apuntes.$idx.factura_proveedor")),
                    'estado' => $this->normalizarEstado($request->input("apuntes.$idx.factura_estado")),
                    'importe_linea' => (float) $linea['debe'],
                    'fecha_documento' => $asiento->fecha,
                ]);
            } elseif ($existing) {
                $existing->update([
                    'proveedor' => $this->trimOrNull($request->input("apuntes.$idx.factura_proveedor")),
                    'estado' => $this->normalizarEstado($request->input("apuntes.$idx.factura_estado")),
                    'importe_linea' => (float) $linea['debe'],
                    'fecha_documento' => $asiento->fecha,
                ]);
            }
        }
    }

    private function trimOrNull(mixed $v): ?string
    {
        if ($v === null) {
            return null;
        }
        $s = trim((string) $v);

        return $s === '' ? null : $s;
    }

    private function normalizarEstado(mixed $v): string
    {
        $s = trim((string) $v);

        return $s === DocumentoGasto::ESTADO_PAGADA ? DocumentoGasto::ESTADO_PAGADA : DocumentoGasto::ESTADO_PENDIENTE;
    }

    private function resolverProveedorId(?string $texto): ?int
    {
        if ($texto === null || $texto === '') {
            return null;
        }

        $id = Proveedor::query()
            ->where('razon_social', $texto)
            ->value('id');

        if ($id !== null) {
            return (int) $id;
        }

        $id = Proveedor::query()
            ->where('nombre_comercial', $texto)
            ->value('id');

        return $id !== null ? (int) $id : null;
    }
}
